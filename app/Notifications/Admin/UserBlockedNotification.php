<?php

declare(strict_types=1);

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserBlockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $reason,
        public ?string $expiresAt = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->error()
            ->subject('Sua conta no Drafto foi suspensa')
            ->greeting('Olá, ' . $notifiable->display_name)
            ->line('Informamos que sua conta foi bloqueada por nossa equipe de moderação.')
            ->line('**Motivo:** ' . $this->reason);

        if ($this->expiresAt) {
            $message->line('A suspensão é temporária e expira em: ' . $this->expiresAt);
        } else {
            $message->line('Esta é uma suspensão permanente.');
        }

        return $message
            ->line('Se você acredita que isso foi um erro, entre em contato com nosso suporte.')
            ->action('Ver Diretrizes da Comunidade', url('/diretrizes'));
    }
}
