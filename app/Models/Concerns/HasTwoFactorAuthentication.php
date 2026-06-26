<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * Helpers de autenticação em dois fatores (2FA).
 */
trait HasTwoFactorAuthentication
{
    /**
     * Determine if the user has two factor authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) &&
               !is_null($this->two_factor_confirmed_at);
    }
}
