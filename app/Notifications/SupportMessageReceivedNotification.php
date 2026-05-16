<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DTOs\SupportContactData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportMessageReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportContactData $data,
    ) {}

    public function via($notifiable): array
    {
        // Se estivermos enviando para uma rota (email direto), usamos apenas mail
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        // Se for um modelo de usuário (Admins), enviamos apenas para o database
        // para evitar duplicidade de e-mail no super admin
        return ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->mailer('support')
            ->subject(__('notifications.support.subject', ['subject' => $this->data->subject]))
            ->greeting(__('notifications.support.greeting'))
            ->line(__('notifications.support.received', [
                'name' => $this->data->name,
                'email' => $this->data->email,
            ]))
            ->line(__('notifications.support.subject_line', ['subject' => $this->data->subject]))
            ->line(__('notifications.support.message_line'))
            ->line($this->data->message)
            ->line(__('notifications.support.respond'))
            ->action(__('notifications.support.action'), url('/dashboard/admin/suporte'))
            ->line(__('notifications.support.thanks'));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'support_received',
            'causer_name' => $this->data->name,
            'message' => 'notifications.support.database_admin_message',
            'link' => '/dashboard/admin/suporte',
        ];
    }
}
