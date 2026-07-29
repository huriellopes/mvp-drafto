<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Enums\ModuleEnum;
use App\Http\Middleware\CheckModuleAccess;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;

function runModuleAccess(?User $user, string $slug, bool $json = false)
{
    $request = Request::create('/dashboard/encurtador');
    $request->setUserResolver(fn () => $user);

    if ($json) {
        $request->headers->set('Accept', 'application/json');
    }

    return (new CheckModuleAccess)->handle(
        $request,
        fn () => response('ok'),
        $slug,
    );
}

function grantModule(User $user, string $slug): void
{
    $module = Module::where('slug', $slug)->firstOrFail();

    $user->modules()->syncWithoutDetaching([
        $module->id => ['is_enabled' => true, 'settings' => json_encode([])],
    ]);
}

it('allows a user that has the module enabled', function () {
    $user = User::factory()->create();
    grantModule($user, ModuleEnum::LINK_SHORTENER->value);

    $response = runModuleAccess($user->fresh(), ModuleEnum::LINK_SHORTENER->value);

    expect($response->getContent())->toBe('ok');
});

it('always allows super admins', function () {
    $user = User::factory()->superAdmin()->create();

    $response = runModuleAccess($user, ModuleEnum::LINK_SHORTENER->value);

    expect($response->getContent())->toBe('ok');
});

it('redirects to the dashboard when the module is unavailable', function () {
    $user = User::factory()->create();
    $user->modules()->detach();

    $response = runModuleAccess($user->fresh(), ModuleEnum::LINK_SHORTENER->value);

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain(route('dashboard.index'));
});

it('returns a 403 json response for json requests without access', function () {
    $user = User::factory()->create();
    $user->modules()->detach();

    $response = runModuleAccess($user->fresh(), ModuleEnum::LINK_SHORTENER->value, json: true);

    expect($response->getStatusCode())->toBe(403);
});

it('redirects guests to the dashboard', function () {
    $response = runModuleAccess(null, ModuleEnum::LINK_SHORTENER->value);

    expect($response->getStatusCode())->toBe(302);
});
