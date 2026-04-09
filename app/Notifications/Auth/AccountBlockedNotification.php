<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountBlockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->error()
            ->subject('Sua conta no Drafto foi bloqueada')
            ->line('Detectamos múltiplas tentativas de login sem sucesso na sua conta.')
            ->line('Por motivos de segurança, sua conta foi temporariamente bloqueada.')
            ->action('Solicitar Desbloqueio', route('password.request'))
            ->line('Se não foi você, recomendamos redefinir sua senha imediatamente.');
    }
}
