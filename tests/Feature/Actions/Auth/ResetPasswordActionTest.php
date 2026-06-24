<?php

declare(strict_types=1);

use App\Actions\Auth\ResetPasswordAction;
use App\DTOs\ResetPasswordData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->action = app(ResetPasswordAction::class);
});

it('resets the password with a valid token and fires the PasswordReset event', function () {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::createToken($user);

    $this->action->exec(new ResetPasswordData(
        token: $token,
        email: $user->email,
        password: 'new-secure-password',
        password_confirmation: 'new-secure-password',
    ));

    $fresh = $user->fresh();

    expect(Hash::check('new-secure-password', $fresh->password))->toBeTrue()
        ->and(Hash::check('old-password', $fresh->password))->toBeFalse();

    Event::assertDispatched(PasswordReset::class);
});

it('throws a validation exception when the token is invalid', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $this->action->exec(new ResetPasswordData(
        token: 'invalid-token',
        email: $user->email,
        password: 'new-secure-password',
        password_confirmation: 'new-secure-password',
    ));
})->throws(ValidationException::class);

it('does not change the password when the token is invalid', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    try {
        $this->action->exec(new ResetPasswordData(
            token: 'invalid-token',
            email: $user->email,
            password: 'new-secure-password',
            password_confirmation: 'new-secure-password',
        ));
    } catch (ValidationException) {
        // expected
    }

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});
