<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Component;

class ResendVerification extends Component
{
    public bool $sent = false;

    public function resend(): void
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return;
        }

        if (RateLimiter::tooManyAttempts('resend-email:' . $user->id, 1)) {
            $this->addError('resend', 'Aguarde um pouco antes de solicitar outro e-mail.');

            return;
        }

        $user->sendEmailVerificationNotification();
        RateLimiter::hit('resend-email:' . $user->id, 120);

        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.auth.resend-verification');
    }
}
