<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use App\Models\User;
use App\Support\PlatformVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

it('returns the configured version (APP_VERSION) when set', function () {
    config()->set('app.version', 'v2.3.4');

    expect(PlatformVersion::current())->toBe('v2.3.4')
        ->and(platform_version())->toBe('v2.3.4');
});

it('falls back to "dev" when not configured and no git tag is available', function () {
    config()->set('app.version', 'dev');
    Cache::flush();
    Process::fake(['*' => Process::result(exitCode: 128)]);

    expect(PlatformVersion::current())->toBe('dev');
});

it('uses the latest git tag when not configured', function () {
    config()->set('app.version', 'dev');
    Cache::flush();
    Process::fake(['git describe*' => Process::result(output: "v3.1.4\n")]);

    expect(PlatformVersion::current())->toBe('v3.1.4');
})->skip(!is_dir(__DIR__ . '/../../../.git'), 'Requer repositório git.');

it('resolves the version from the latest GitHub release in production', function () {
    config()->set('app.version', 'dev');
    config()->set('app.github_repo', 'huriellopes/mvp-drafto');
    Cache::flush();

    // Fora de local/testing para habilitar a consulta remota.
    $this->app->detectEnvironment(fn () => 'production');

    Process::fake(['*' => Process::result(exitCode: 128)]); // git sem tag
    Http::fake([
        'api.github.com/*' => Http::response(['tag_name' => 'v9.9.9']),
    ]);

    expect(PlatformVersion::current())->toBe('v9.9.9');
    Http::assertSent(fn ($request) => str_contains($request->url(), 'releases/latest'));
});

it('does not call GitHub outside production (no network in local/testing)', function () {
    config()->set('app.version', 'dev');
    Cache::flush();
    Process::fake(['*' => Process::result(exitCode: 128)]);
    Http::fake();

    expect(PlatformVersion::current())->toBe('dev');
    Http::assertNothingSent();
});

it('shows the platform version in the dashboard sidebar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertSee('v0.0.0-test'); // definido em phpunit.xml (APP_VERSION)
});
