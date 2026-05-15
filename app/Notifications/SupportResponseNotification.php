<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Support;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SupportResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Support $support,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('notifications.support.response.subject', ['subject' => $this->support->subject]))
            ->greeting(__('notifications.support.response.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.support.response.body'))
            ->line(__('notifications.support.response.status', ['status' => $this->support->status->label()]))
            ->line(__('notifications.support.response.admin_response', ['response' => $this->support->admin_response]))
            ->action(__('notifications.support.response.action'), url('/dashboard/suporte'))
            ->line(__('notifications.support.response.thanks'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'support_id' => $this->support->id,
            'status' => $this->support->status->value,
            'causer_name' => 'Equipe Drafto',
            'message' => 'notifications.support.response.database_message',
            'link' => '/dashboard/suporte',
        ];
    }
}
