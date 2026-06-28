<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\User;

function enableModuleForUser(User $user, ModuleEnum $moduleEnum, array $settings = [], bool $enabled = true): void
{
    $module = Module::where('slug', $moduleEnum->value)->firstOrFail();

    $user->modules()->syncWithoutDetaching([
        $module->id => [
            'is_enabled' => $enabled,
            'settings' => json_encode($settings),
        ],
    ]);
}

it('always reports modules available for super admins', function () {
    $admin = User::factory()->superAdmin()->create();

    expect($admin->isModuleAvailable(ModuleEnum::LINK_SHORTENER))->toBeTrue()
        ->and($admin->isModuleAvailable(ModuleEnum::LINK_SHORTENER->value))->toBeTrue();
});

it('reports a module unavailable when the user has no pivot for it', function () {
    $user = User::factory()->writer()->create();
    // The UserObserver auto-provisions all modules, so detach to exercise the "no pivot" branch.
    $user->modules()->detach();

    expect($user->fresh()->isModuleAvailable(ModuleEnum::LINK_SHORTENER))->toBeFalse();
});

it('reports a module available when both module and pivot are enabled', function () {
    // Provisioned by the UserObserver on creation.
    $user = User::factory()->writer()->create();

    expect($user->isModuleAvailable(ModuleEnum::LINK_SHORTENER))->toBeTrue();
});

it('reports a module unavailable when the pivot is disabled', function () {
    $user = User::factory()->writer()->create();
    enableModuleForUser($user, ModuleEnum::LINK_SHORTENER, [], enabled: false);

    expect($user->fresh()->isModuleAvailable(ModuleEnum::LINK_SHORTENER))->toBeFalse();
});

it('returns a module setting from the pivot', function () {
    $user = User::factory()->writer()->create();
    enableModuleForUser($user, ModuleEnum::LINK_SHORTENER, ['enable_for_posts' => false]);

    expect($user->fresh()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_posts'))->toBeFalse();
});

it('returns the default when the module setting is missing', function () {
    $user = User::factory()->writer()->create();
    enableModuleForUser($user, ModuleEnum::LINK_SHORTENER, []);

    expect($user->fresh()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'unknown', 'default'))->toBe('default');
});

it('returns the default when the user has no pivot for the module', function () {
    $user = User::factory()->writer()->create();
    $user->modules()->detach();

    expect($user->fresh()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_posts', 'def'))->toBe('def');
});

it('exposes the fixed free plan slug and name', function () {
    $user = User::factory()->create();

    expect($user->getPlanSlug())->toBe('free')
        ->and($user->getPlanName())->toBe('Gratuito');
});
