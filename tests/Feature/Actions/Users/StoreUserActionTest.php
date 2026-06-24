<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\StoreUserAction;
use App\DTOs\SaveUserData;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('creates a user with a profile and forces password change', function () {
    Event::fake([Registered::class]);

    $data = new SaveUserData(
        name: 'Jane Doe',
        email: 'jane@example.com',
        password: 'secret-password',
        role: RoleEnum::WRITER,
        status: UserStatusEnum::ACTIVE,
        send_welcome_email: true,
    );

    $user = app(StoreUserAction::class)->exec($data);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Jane Doe')
        ->and($user->email)->toBe('jane@example.com')
        ->and($user->role)->toBe(RoleEnum::WRITER)
        ->and($user->status)->toBe(UserStatusEnum::ACTIVE)
        ->and($user->must_change_password)->toBeTrue()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($user->profile)->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'jane@example.com',
        'must_change_password' => true,
    ]);

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
    ]);
});

it('dispatches the Registered event when a welcome email is requested', function () {
    Event::fake([Registered::class]);

    $data = new SaveUserData(
        name: 'John Smith',
        email: 'john@example.com',
        password: 'another-password',
        role: RoleEnum::READER,
        send_welcome_email: true,
    );

    $user = app(StoreUserAction::class)->exec($data);

    Event::assertDispatched(Registered::class, fn (Registered $event) => $event->user->is($user));
});

it('does not dispatch the Registered event when welcome email is disabled', function () {
    Event::fake([Registered::class]);

    $data = new SaveUserData(
        name: 'Silent User',
        email: 'silent@example.com',
        password: 'no-email-password',
        role: RoleEnum::READER,
        send_welcome_email: false,
    );

    app(StoreUserAction::class)->exec($data);

    Event::assertNotDispatched(Registered::class);
});
