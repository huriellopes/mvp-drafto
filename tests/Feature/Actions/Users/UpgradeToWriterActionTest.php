<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\UpgradeToWriterAction;
use App\Enums\RoleEnum;
use App\Models\Profile;
use App\Models\User;

it('upgrades a reader to writer and creates a verified profile', function () {
    $user = User::factory()->reader()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    expect($user->profile()->exists())->toBeFalse();

    app(UpgradeToWriterAction::class)->exec($user);

    expect($user->refresh()->role)->toBe(RoleEnum::WRITER)
        ->and($user->profile)->not->toBeNull()
        ->and($user->profile->name)->toBe('Jane Doe')
        ->and($user->profile->email)->toBe('jane@example.com')
        ->and($user->profile->is_verified)->toBeTrue();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => RoleEnum::WRITER->value,
    ]);

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'is_verified' => true,
    ]);
});

it('updates the existing profile instead of creating a new one', function () {
    $user = User::factory()->reader()->create([
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);

    $profile = Profile::factory()->create([
        'user_id' => $user->id,
    ]);

    app(UpgradeToWriterAction::class)->exec($user);

    expect($user->refresh()->role)->toBe(RoleEnum::WRITER)
        ->and(Profile::query()->where('user_id', $user->id)->count())->toBe(1);

    $this->assertDatabaseHas('profiles', [
        'id' => $profile->id,
        'user_id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('does nothing when the user is not a reader', function () {
    $user = User::factory()->writer()->create();

    app(UpgradeToWriterAction::class)->exec($user);

    expect($user->refresh()->role)->toBe(RoleEnum::WRITER)
        ->and($user->profile()->exists())->toBeFalse();
});
