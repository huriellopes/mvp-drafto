<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use RuntimeException;

// Isola o throttle de alertas (usa o cache) entre os testes.
beforeEach(fn () => Cache::flush());

it('serves the branded error page (not the native Laravel one) for common statuses', function (int $status, string $needle) {
    Route::get("/__err/{$status}", fn () => abort($status))->middleware('web');

    $this->get("/__err/{$status}")
        ->assertStatus($status)
        ->assertSee($needle)
        ->assertSee('Drafto'); // layout branded
})->with([
    '400' => [400, 'Pedido truncado'],
    '401' => [401, 'Acesso reservado'],
    '403' => [403, 'Status Error 403'],
    '404' => [404, 'Caminho sem saída'],
    '429' => [429, 'Calma, escritor'],
]);

it('redirects an unauthenticated request to login instead of rendering a 500', function () {
    // Cenário real: a sessão foi invalidada (ex.: login em outra máquina via
    // EnforceSingleSession) e o usuário acessa uma rota protegida. O middleware
    // auth lança AuthenticationException — o handler nativo deve redirecionar para
    // o login, e o BrandedErrorRenderer NÃO pode abafar isso como erro 500.
    config(['app.debug' => false]);

    Route::get('/__needs-auth', fn () => 'secret')->middleware(['web', 'auth']);

    $this->get('/__needs-auth')
        ->assertRedirect(route('login'));
});

it('serves the branded 404 for an unknown url', function () {
    $this->get('/esta-rota-nao-existe-' . uniqid())
        ->assertNotFound()
        ->assertSee('Caminho sem saída');
});

it('captures 500 errors: alerts Telegram and shows the error code on the branded page', function () {
    config(['app.debug' => false, 'services.telegram.token' => 'TEST_TOKEN', 'services.telegram.chat' => '1']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    Route::get('/__boom', fn () => throw new RuntimeException('explosão de teste'))->middleware('web');

    $response = $this->get('/__boom');

    $response->assertStatus(500)
        ->assertSee('Tinta derramada')   // tela 500 branded
        ->assertSee('Código do erro');   // trace_id exibido para correlação

    // Erro 500 alertado no Telegram em tempo real.
    Http::assertSent(fn ($request) => str_contains($request->url(), 'api.telegram.org')
        && str_contains($request['text'] ?? '', 'Erro 500'));
});

it('also alerts Telegram for 4xx errors (everything is alerted)', function () {
    config(['services.telegram.token' => 'TEST_TOKEN', 'services.telegram.chat' => '1']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    Route::get('/__forbidden', fn () => abort(403))->middleware('web');

    $this->get('/__forbidden')->assertStatus(403);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'api.telegram.org')
        && str_contains($request['text'] ?? '', 'Erro 403'));
});

it('alerts Telegram for 404s as well', function () {
    config(['services.telegram.token' => 'TEST_TOKEN', 'services.telegram.chat' => '1']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    $this->get('/rota-fantasma-' . uniqid())->assertNotFound();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'api.telegram.org')
        && str_contains($request['text'] ?? '', 'Erro 404'));
});

it('does NOT alert Telegram for 404s from automated scanner paths', function (string $path) {
    config(['services.telegram.token' => 'TEST_TOKEN', 'services.telegram.chat' => '1']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    $this->get($path)->assertNotFound();

    // Ruído de varredura de bot: registrado em arquivo, porém sem alerta em tempo real.
    Http::assertNotSent(fn ($request) => str_contains($request->url(), 'api.telegram.org'));
})->with([
    'wordpress css' => ['/wp-includes/css/buttons.css'],
    'wp-login' => ['/wp-login.php'],
    'env file' => ['/.env'],
    'phpunit rce probe' => ['/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php'],
    'exchange owa probe' => ['/owa/auth/x.js'],
]);

it('registers a per-IP public-content rate limiter at 120/min', function () {
    $limiter = RateLimiter::limiter('public-content');

    expect($limiter)->not->toBeNull();

    $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '203.0.113.7']);
    $limit = $limiter($request);

    expect($limit->maxAttempts)->toBe(120)
        ->and($limit->key)->toBe('203.0.113.7');
});

it('serves a dynamic robots.txt pointing at the current host sitemap', function () {
    $response = $this->get('/robots.txt')->assertOk();

    $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    expect($response->getContent())
        ->toContain('Sitemap: ' . url('/sitemap.xml'))
        ->toContain('Disallow: /dashboard/')
        ->not->toContain('drafto.test');
});

it('serves a valid RFC 9116 security.txt instead of a 404', function () {
    $response = $this->get('/.well-known/security.txt')->assertOk();

    $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
    expect($response->getContent())
        ->toContain('Contact: mailto:' . config('support.email'))
        ->toContain('Expires:');
});

it('throttles repeated alerts for the same error within the window', function () {
    config(['services.telegram.token' => 'TEST_TOKEN', 'services.telegram.chat' => '1']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    Route::get('/__repeat', fn () => abort(500, 'erro repetido'))->middleware('web');

    $this->get('/__repeat')->assertStatus(500);
    $this->get('/__repeat')->assertStatus(500); // mesma assinatura → throttled

    Http::assertSentCount(1);
});
