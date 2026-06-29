<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use PragmaRX\Google2FALaravel\Google2FA;

final readonly class ConfirmTwoFactorAuthAction
{
    public function __construct(
        private Google2FA $google2fa,
    ) {}

    public function exec(User $user, string $code): bool
    {
        if (is_null($user->two_factor_secret)) {
            return false;
        }

        $isValid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $code,
        );

        if ($isValid) {
            $user->forceFill([
                'two_factor_confirmed_at' => now(),
            ])->save();

            return true;
        }

        return false;
    }
}
