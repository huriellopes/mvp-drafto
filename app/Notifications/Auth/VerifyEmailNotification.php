<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject(Lang::get('Confirme seu e-mail na Drafto'))
            ->view('emails.auth.verify-email', [
                'user' => $notifiable,
                'url' => $verificationUrl,
            ]);
    }
}
