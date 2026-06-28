<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\DTOs\AuthenticateData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->action = app(AuthenticateUserAction::class);
});

it('returns false when 2FA is enabled but credentials are invalid', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
        'two_factor_secret' => encrypt('SECRET'),
        'two_factor_confirmed_at' => now(),
    ]);

    $result = $this->action->exec(AuthenticateData::from([
        'email' => $user->email,
        'password' => 'wrong-password',
    ]));

    expect($result)->toBeFalse();
});

it('returns two-factor when 2FA is enabled and credentials are valid', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
        'two_factor_secret' => encrypt('SECRET'),
        'two_factor_confirmed_at' => now(),
    ]);

    $result = $this->action->exec(AuthenticateData::from([
        'email' => $user->email,
        'password' => 'correct-password',
    ]));

    expect($result)->toBe('two-factor')
        ->and(session('auth.2fa.id'))->toBe($user->id);
});

it('throws a validation exception when the account is banned', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret-pass'),
        'banned_until' => now()->addDays(10),
    ]);

    expect(fn () => $this->action->exec(AuthenticateData::from([
        'email' => $user->email,
        'password' => 'secret-pass',
    ])))->toThrow(ValidationException::class);

    expect(auth()->check())->toBeFalse();
});
