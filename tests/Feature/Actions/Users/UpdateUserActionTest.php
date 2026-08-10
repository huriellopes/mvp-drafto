<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\UpdateUserAction;
use App\DTOs\UpdateUserData;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates the user name, email and role', function () {
    $user = User::factory()->reader()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $result = app(UpdateUserAction::class)->exec($user, new UpdateUserData(
        name: 'New Name',
        email: 'new@example.com',
        role: RoleEnum::WRITER,
    ));

    expect($result)->toBeTrue();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
        'role' => RoleEnum::WRITER->value,
    ]);
});

it('updates and hashes the password when provided', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    app(UpdateUserAction::class)->exec($user, new UpdateUserData(
        password: 'new-secret-password',
    ));

    $user->refresh();

    expect($user->password)->not->toBe($originalPassword)
        ->and(Hash::check('new-secret-password', $user->password))->toBeTrue();
});

it('does not change the password when it is empty and keeps other fields untouched', function () {
    $user = User::factory()->create([
        'name' => 'Keep Me',
    ]);
    $originalPassword = $user->password;

    app(UpdateUserAction::class)->exec($user, new UpdateUserData(
        password: null,
    ));

    $user->refresh();

    expect($user->password)->toBe($originalPassword)
        ->and($user->name)->toBe('Keep Me');
});

it('refuses to promote a user to super_admin when the acting user is not already super_admin', function () {
    $actor = User::factory()->create(['role' => RoleEnum::WRITER]);
    $target = User::factory()->reader()->create();

    $this->actingAs($actor);

    $result = app(UpdateUserAction::class)->exec($target, new UpdateUserData(
        role: RoleEnum::SUPER_ADMIN,
    ));

    expect($result)->toBeFalse();

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'role' => RoleEnum::READER->value,
    ]);
});

it('allows promoting a user to super_admin when the acting user already is super_admin', function () {
    $actor = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $target = User::factory()->reader()->create();

    $this->actingAs($actor);

    $result = app(UpdateUserAction::class)->exec($target, new UpdateUserData(
        role: RoleEnum::SUPER_ADMIN,
    ));

    expect($result)->toBeTrue();

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'role' => RoleEnum::SUPER_ADMIN->value,
    ]);
});
