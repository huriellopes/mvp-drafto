<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

final class WelcomeNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $password,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->mailer('contact')
            ->subject(__('Bem-vindo à Drafto!'))
            ->view('emails.auth.welcome', [
                'user' => $notifiable,
                'password' => $this->password,
                'url' => route('login'),
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
