<?php

declare(strict_types=1);

use App\Actions\Auth\AuthenticateUserAction;
use App\DTOs\AuthenticateData;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->action = app(AuthenticateUserAction::class);
});

it('authenticates a user with valid credentials and updates login metadata', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
        'status' => UserStatusEnum::ACTIVE,
        'reengagement_stage' => 3,
        'reengagement_sent_at' => now()->subDay(),
    ]);

    $result = $this->action->exec(new AuthenticateData(
        email: $user->email,
        password: 'secret-password',
    ));

    expect($result)->toBeTrue()
        ->and(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($user->id);

    $fresh = $user->fresh();

    expect($fresh->reengagement_stage)->toBeNull()
        ->and($fresh->reengagement_sent_at)->toBeNull()
        ->and($fresh->last_login_at)->not->toBeNull();
});

it('returns false when the password is invalid', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    $result = $this->action->exec(new AuthenticateData(
        email: $user->email,
        password: 'wrong-password',
    ));

    expect($result)->toBeFalse()
        ->and(Auth::check())->toBeFalse();
});

it('throws a validation exception and logs out when the account is inactive', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
        'status' => UserStatusEnum::INACTIVE,
    ]);

    $this->action->exec(new AuthenticateData(
        email: $user->email,
        password: 'secret-password',
    ));
})->throws(ValidationException::class);

it('does not authenticate inactive users', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
        'status' => UserStatusEnum::INACTIVE,
    ]);

    try {
        $this->action->exec(new AuthenticateData(
            email: $user->email,
            password: 'secret-password',
        ));
    } catch (ValidationException) {
        // expected
    }

    expect(Auth::check())->toBeFalse();
});

it('returns the two-factor challenge when 2FA is enabled', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
        'two_factor_secret' => 'fake-secret',
        'two_factor_confirmed_at' => now(),
    ]);

    $result = $this->action->exec(new AuthenticateData(
        email: $user->email,
        password: 'secret-password',
        remember: true,
    ));

    expect($result)->toBe('two-factor')
        ->and(Auth::check())->toBeFalse()
        ->and(session('auth.2fa.id'))->toBe($user->id)
        ->and(session('auth.2fa.remember'))->toBeTrue();
});
