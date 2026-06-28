<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Enums\RoleEnum;
use App\Models\Module;
use App\Models\User;

it('syncs all globally enabled modules to a user on creation', function () {
    $enabledCount = Module::query()->where('is_enabled', true)->count();

    $user = User::factory()->create();

    expect($user->modules()->count())->toBe($enabledCount)
        ->and($enabledCount)->toBeGreaterThan(0);

    $user->modules->each(function ($module) {
        expect((bool) $module->pivot->is_enabled)->toBeTrue();
    });
});

it('does not include globally disabled modules', function () {
    $module = Module::query()->first();
    $module->update(['is_enabled' => false]);

    $user = User::factory()->create();

    expect($user->modules()->pluck('modules.id'))->not->toContain($module->id);
});

it('re-syncs modules when the user role changes', function () {
    $user = User::factory()->create(['role' => RoleEnum::READER]);

    // Detach everything to prove the observer re-syncs on a role change.
    $user->modules()->detach();
    expect($user->fresh()->modules()->count())->toBe(0);

    $user->update(['role' => RoleEnum::WRITER]);

    expect($user->fresh()->modules()->count())
        ->toBe(Module::query()->where('is_enabled', true)->count());
});

it('does not re-sync modules when an unrelated field changes', function () {
    $user = User::factory()->create();

    $user->modules()->detach();
    expect($user->fresh()->modules()->count())->toBe(0);

    $user->update(['name' => 'New Name']);

    expect($user->fresh()->modules()->count())->toBe(0);
});
