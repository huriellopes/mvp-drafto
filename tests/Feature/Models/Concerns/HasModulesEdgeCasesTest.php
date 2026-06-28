<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\User;

function attachModuleWithSettings(User $user, mixed $settings): void
{
    $module = Module::where('slug', ModuleEnum::LINK_SHORTENER->value)->firstOrFail();

    $user->modules()->syncWithoutDetaching([
        $module->id => [
            'is_enabled' => true,
            'settings' => $settings,
        ],
    ]);
}

it('reads a module setting decoding a JSON string pivot value', function () {
    $user = User::factory()->create();
    attachModuleWithSettings($user, json_encode(['enable_for_profile' => false]));

    $value = $user->fresh()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_profile', true);

    expect($value)->toBeFalse();
});

it('reads a module setting from an already-array pivot value', function () {
    $user = User::factory()->create();
    // Some casts return an array directly; force the non-string branch (line 75).
    attachModuleWithSettings($user, ['enable_for_posts' => false]);

    $value = $user->fresh()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_posts', true);

    expect($value)->toBeFalse();
});

it('returns the default when the user does not have the module', function () {
    $user = User::factory()->create();
    $user->modules()->detach();

    $value = $user->fresh()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_posts', 'fallback');

    expect($value)->toBe('fallback');
});
