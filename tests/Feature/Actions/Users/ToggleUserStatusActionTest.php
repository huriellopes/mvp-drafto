<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\ToggleUserStatusAction;
use App\Enums\UserStatusEnum;
use App\Models\User;

it('suspends an active user when no target status is given', function () {
    $user = User::factory()->active()->create();

    $result = app(ToggleUserStatusAction::class)->exec($user);

    expect($result)->toBeTrue()
        ->and($user->status)->toBe(UserStatusEnum::SUSPENDED);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'status' => UserStatusEnum::SUSPENDED->value,
    ]);
});

it('activates a non-active user when no target status is given', function () {
    $user = User::factory()->suspended()->create();

    $result = app(ToggleUserStatusAction::class)->exec($user);

    expect($result)->toBeTrue()
        ->and($user->status)->toBe(UserStatusEnum::ACTIVE);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
});

it('applies the explicit target status when provided', function () {
    $user = User::factory()->active()->create();

    $result = app(ToggleUserStatusAction::class)->exec($user, UserStatusEnum::BANNED);

    expect($result)->toBeTrue()
        ->and($user->status)->toBe(UserStatusEnum::BANNED);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'status' => UserStatusEnum::BANNED->value,
    ]);
});
