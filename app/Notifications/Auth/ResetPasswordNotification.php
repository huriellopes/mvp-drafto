<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(Lang::get('Recuperação de senha na Drafto'))
            ->view('emails.auth.reset-password', [
                'user' => $notifiable,
                'url' => url(route('password.reset', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false)),
            ]);
    }
}
