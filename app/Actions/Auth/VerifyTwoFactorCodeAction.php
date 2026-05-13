<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Collection;
use PragmaRX\Google2FALaravel\Google2FA;

final class VerifyTwoFactorCodeAction
{
    public function __construct(
        private readonly Google2FA $google2fa,
    ) {}

    public function exec(User $user, string $code): bool
    {
        // 1. Tentar validar como TOTP
        if ($this->google2fa->verifyKey($user->two_factor_secret, $code)) {
            return true;
        }

        // 2. Tentar validar como Recovery Code
        $recoveryCodes = Collection::make($user->two_factor_recovery_codes);

        if ($recoveryCodes->contains($code)) {
            $user->forceFill([
                'two_factor_recovery_codes' => $recoveryCodes->reject(fn ($c) => $c === $code)->values()->toArray(),
            ])->save();

            return true;
        }

        return false;
    }
}
