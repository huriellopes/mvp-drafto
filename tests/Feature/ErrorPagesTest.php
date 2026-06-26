<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

it('throttles repeated alerts for the same error within the window', function () {
    config(['services.telegram.token' => 'TEST_TOKEN', 'services.telegram.chat' => '1']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    Route::get('/__repeat', fn () => abort(500, 'erro repetido'))->middleware('web');

    $this->get('/__repeat')->assertStatus(500);
    $this->get('/__repeat')->assertStatus(500); // mesma assinatura → throttled

    Http::assertSentCount(1);
});
