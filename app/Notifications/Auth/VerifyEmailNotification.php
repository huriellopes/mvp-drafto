<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Override;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable, SerializesModels;

    #[Override]
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->mailer('contact')
            ->subject(__('Confirme seu e-mail na Drafto'))
            ->view('emails.auth.verify-email', [
                'user' => $notifiable,
                'url' => $verificationUrl,
            ]);
    }
}
