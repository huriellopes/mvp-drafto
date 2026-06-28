<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Profile;

use App\Enums\ModuleEnum;
use App\Livewire\Dashboard\Profile\ProfileBadgeGenerator;
use App\Models\Module;
use App\Models\User;
use Livewire\Livewire;

/**
 * Covers the "brand" theme branch of mount() (line 30): when the user's
 * PROFILE_BADGE module exposes "brand" in themes_available, it becomes default.
 */
it('defaults to the brand theme when the user may use brand', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $module = Module::where('slug', ModuleEnum::PROFILE_BADGE->value)->firstOrFail();
    $user->modules()->syncWithoutDetaching([
        $module->id => [
            'is_enabled' => true,
            'settings' => json_encode(['themes_available' => ['dark', 'brand']]),
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(ProfileBadgeGenerator::class)
        ->assertSet('form.theme', 'brand');
});

/**
 * Covers the "dark" fallback branch of mount() (line 31): no brand access.
 */
it('falls back to the dark theme when brand is not available', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $module = Module::where('slug', ModuleEnum::PROFILE_BADGE->value)->firstOrFail();
    $user->modules()->syncWithoutDetaching([
        $module->id => [
            'is_enabled' => true,
            'settings' => json_encode(['themes_available' => ['dark']]),
        ],
    ]);

    $this->actingAs($user);

    Livewire::test(ProfileBadgeGenerator::class)
        ->assertSet('form.theme', 'dark');
});
