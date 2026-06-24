<?php

declare(strict_types=1);

use App\Actions\Auth\ConfirmTwoFactorAuthAction;
use App\Models\User;
use PragmaRX\Google2FALaravel\Google2FA;

beforeEach(function () {
    $this->action = app(ConfirmTwoFactorAuthAction::class);
    $this->google2fa = app(Google2FA::class);
});

it('confirms two-factor auth with a valid code', function () {
    $secret = $this->google2fa->generateSecretKey();

    $user = User::factory()->create([
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => null,
    ]);

    $code = $this->google2fa->getCurrentOtp($secret);

    $result = $this->action->exec($user, $code);

    expect($result)->toBeTrue()
        ->and($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

it('returns false when the code is invalid', function () {
    $secret = $this->google2fa->generateSecretKey();

    $user = User::factory()->create([
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => null,
    ]);

    $result = $this->action->exec($user, '000000');

    expect($result)->toBeFalse()
        ->and($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

it('returns false when the user has no two-factor secret', function () {
    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $result = $this->action->exec($user, '123456');

    expect($result)->toBeFalse()
        ->and($user->fresh()->two_factor_confirmed_at)->toBeNull();
});
