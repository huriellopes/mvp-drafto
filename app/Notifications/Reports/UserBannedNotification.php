<?php

declare(strict_types=1);

namespace App\Notifications\Reports;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

final class UserBannedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Carbon $bannedUntil,
        public string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->error()
            ->subject('Sua conta foi suspensa - ' . config('app.name'))
            ->greeting("Olá, {$notifiable->name}.")
            ->line('Lamentamos informar que sua conta foi suspensa temporariamente devido à violação das nossas diretrizes.')
            ->line('**Motivo da suspensão:** ' . $this->reason)
            ->line('Sua conta permanecerá bloqueada até: **' . $this->bannedUntil->translatedFormat('d \d\e F \d\e Y \à\s H:i') . '**')
            ->line('Se você acredita que isso foi um erro, entre em contato com o suporte.')
            ->action('Revisar Termos de Uso', url('/termos'));
    }
}
