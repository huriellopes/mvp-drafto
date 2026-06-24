<?php

declare(strict_types=1);

use App\Actions\Auth\VerifyTwoFactorCodeAction;
use App\Models\User;
use PragmaRX\Google2FALaravel\Google2FA;

beforeEach(function () {
    $this->action = app(VerifyTwoFactorCodeAction::class);
    $this->google2fa = app(Google2FA::class);
});

it('verifies a valid TOTP code', function () {
    $secret = $this->google2fa->generateSecretKey();

    $user = User::factory()->create([
        'two_factor_secret' => $secret,
    ]);

    $code = $this->google2fa->getCurrentOtp($secret);

    expect($this->action->exec($user, $code))->toBeTrue();
});

it('returns false for an invalid TOTP code', function () {
    $secret = $this->google2fa->generateSecretKey();

    $user = User::factory()->create([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => ['code-one', 'code-two'],
    ]);

    expect($this->action->exec($user, '000000'))->toBeFalse();
});

it('verifies a valid recovery code and consumes it', function () {
    $secret = $this->google2fa->generateSecretKey();

    $user = User::factory()->create([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => ['recovery-aaa', 'recovery-bbb'],
    ]);

    $result = $this->action->exec($user, 'recovery-aaa');

    expect($result)->toBeTrue()
        ->and($user->fresh()->two_factor_recovery_codes)
        ->toBe(['recovery-bbb']);
});
