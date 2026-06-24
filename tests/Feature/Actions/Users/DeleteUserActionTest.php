<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\DeleteUserAction;
use App\Models\User;

it('deletes another user and archives it in deleted_models', function () {
    $user = User::factory()->create();

    $result = app(DeleteUserAction::class)->exec($user);

    expect($result)->toBeTrue()
        ->and(User::query()->whereKey($user->id)->exists())->toBeFalse();

    $this->assertDatabaseHas('deleted_models', [
        'key' => $user->id,
        'model' => $user->getMorphClass(),
    ]);
});

it('does not allow a user to delete their own account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $result = app(DeleteUserAction::class)->exec($user);

    expect($result)->toBeFalse()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue();
});
