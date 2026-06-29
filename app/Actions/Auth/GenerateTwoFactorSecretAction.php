<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FALaravel\Google2FA;

final readonly class GenerateTwoFactorSecretAction
{
    public function __construct(
        private Google2FA $google2fa,
    ) {}

    public function exec(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => $this->google2fa->generateSecretKey(),
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
        ])->save();
    }

    private function generateRecoveryCodes(): array
    {
        return Collection::times(8, fn () => Str::random(10) . '-' . Str::random(10))
            ->all();
    }
}
