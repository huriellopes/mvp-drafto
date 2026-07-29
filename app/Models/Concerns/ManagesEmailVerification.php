<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;

/**
 * Verificação de e-mail e notificações de autenticação.
 */
trait ManagesEmailVerification
{
    /**
     * Flag para pular o envio do e-mail de verificação padrão (usado no WelcomeNotification).
     */
    public bool $skipVerificationEmail = false;

    public function hasVerificationExpired(): bool
    {
        if ($this->hasVerifiedEmail()) {
            return false;
        }

        return $this->created_at->addDays(15)->isPast();
    }

    public function daysLeftToVerify(): int
    {
        return (int) max(0, now()->diffInDays($this->created_at->addDays(15), false));
    }

    public function sendEmailVerificationNotification(): void
    {
        if ($this->skipVerificationEmail) {
            return;
        }

        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
