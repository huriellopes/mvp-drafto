<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('returns true for a guest when the module is enabled globally', function () {
    expect(is_module_enabled(ModuleEnum::PROFILE))->toBeTrue();
});

it('returns false for an unknown slug', function () {
    expect(is_module_enabled('this-module-does-not-exist'))->toBeFalse();
});

it('always returns true for a super admin (bypass)', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    // Even a disabled module is available to a super admin.
    Module::query()->where('slug', ModuleEnum::PROFILE->value)->update(['is_enabled' => false]);
    Cache::flush();

    expect(is_module_enabled(ModuleEnum::PROFILE))->toBeTrue()
        ->and(is_module_enabled('this-module-does-not-exist'))->toBeTrue();
});

it('returns false when the module is disabled globally', function () {
    Module::query()->where('slug', ModuleEnum::PROFILE->value)->update(['is_enabled' => false]);
    Cache::flush();

    expect(is_module_enabled(ModuleEnum::PROFILE))->toBeFalse();
});

it('returns false for a logged-in user not attached to the module via pivot', function () {
    // The UserObserver auto-syncs all enabled modules on creation, so we detach
    // one to exercise the "no pivot row" branch of isModuleAvailable().
    $user = User::factory()->reader()->create();
    $module = Module::query()->where('slug', ModuleEnum::PROFILE->value)->first();
    $user->modules()->detach($module->id);
    $this->actingAs($user);

    expect(is_module_enabled(ModuleEnum::PROFILE))->toBeFalse();
});

it('returns false when the module pivot is disabled for the user', function () {
    $user = User::factory()->reader()->create();
    $module = Module::query()->where('slug', ModuleEnum::PROFILE->value)->first();
    $user->modules()->updateExistingPivot($module->id, ['is_enabled' => false]);
    $this->actingAs($user);

    expect(is_module_enabled(ModuleEnum::PROFILE))->toBeFalse();
});

it('returns true for a logged-in user with the module enabled in the pivot', function () {
    // Readers are provisioned with all enabled modules on creation.
    $user = User::factory()->reader()->create();
    $this->actingAs($user);

    expect(is_module_enabled(ModuleEnum::PROFILE))->toBeTrue();
});

it('accepts a string slug for an enabled module', function () {
    expect(is_module_enabled(ModuleEnum::PROFILE->value))->toBeTrue();
});
