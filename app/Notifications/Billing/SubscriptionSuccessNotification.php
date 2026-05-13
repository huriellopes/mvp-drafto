<?php

declare(strict_types=1);

namespace App\Notifications\Billing;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

final class SubscriptionSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $planName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Sua assinatura na Drafto está ativa! 🚀')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("É com muita alegria que confirmamos a ativação do seu plano **{$this->planName}**.")
            ->line('Agora você tem acesso a recursos exclusivos para elevar suas histórias a um novo patamar.')
            ->action('Gerenciar minha Assinatura', route('dashboard.billing.index'))
            ->line('Obrigado por apoiar a nossa comunidade de escritores!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Assinatura Ativada',
            'message' => "Seu plano {$this->planName} foi ativado com sucesso.",
            'link' => route('dashboard.billing.index'),
            'type' => 'success',
        ];
    }
}
