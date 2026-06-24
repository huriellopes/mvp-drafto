<?php

declare(strict_types=1);

use App\Actions\Auth\RegisterUserAction;
use App\DTOs\RegisterUserData;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    Http::fake();
    $this->action = app(RegisterUserAction::class);
});

it('registers a writer user with a profile', function () {
    $data = new RegisterUserData(
        name: 'Jane Writer',
        email: 'jane.writer@example.com',
        password: 'secret-password',
        password_confirmation: 'secret-password',
        role: RoleEnum::WRITER->value,
    );

    $user = $this->action->exec($data);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Jane Writer')
        ->and($user->email)->toBe('jane.writer@example.com')
        ->and($user->role)->toBe(RoleEnum::WRITER)
        ->and($user->status)->toBe(UserStatusEnum::ACTIVE)
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($user->last_login_at)->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'jane.writer@example.com',
        'role' => RoleEnum::WRITER->value,
    ]);

    $this->assertDatabaseHas('profiles', [
        'user_id' => $user->id,
        'email' => 'jane.writer@example.com',
        'is_verified' => true,
    ]);

    Notification::assertSentTo($user, WelcomeNotification::class);
});

it('registers a reader user without a profile', function () {
    $data = new RegisterUserData(
        name: 'John Reader',
        email: 'john.reader@example.com',
        password: 'secret-password',
        password_confirmation: 'secret-password',
        role: RoleEnum::READER->value,
    );

    $user = $this->action->exec($data);

    expect($user->role)->toBe(RoleEnum::READER)
        ->and($user->profile()->exists())->toBeFalse();

    $this->assertDatabaseMissing('profiles', [
        'user_id' => $user->id,
    ]);
});

it('falls back to the reader role when an invalid role is given', function () {
    $data = new RegisterUserData(
        name: 'Sneaky Admin',
        email: 'sneaky@example.com',
        password: 'secret-password',
        password_confirmation: 'secret-password',
        role: RoleEnum::SUPER_ADMIN->value,
    );

    $user = $this->action->exec($data);

    expect($user->role)->toBe(RoleEnum::READER)
        ->and($user->profile()->exists())->toBeFalse();
});

it('fires the Registered event for the new user', function () {
    Event::fake([Registered::class]);

    $data = new RegisterUserData(
        name: 'Eventful User',
        email: 'eventful@example.com',
        password: 'secret-password',
        password_confirmation: 'secret-password',
        role: RoleEnum::READER->value,
    );

    $user = $this->action->exec($data);

    Event::assertDispatched(Registered::class, function (Registered $event) use ($user) {
        return $event->user->is($user);
    });
});
