<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DTOs\SupportContactData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class SupportMessageReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportContactData $data
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('notifications.support.subject', ['subject' => $this->data->subject]))
            ->greeting(Lang::get('notifications.support.greeting'))
            ->line(Lang::get('notifications.support.received', [
                'name' => $this->data->name,
                'email' => $this->data->email
            ]))
            ->line(Lang::get('notifications.support.subject_line', ['subject' => $this->data->subject]))
            ->line(Lang::get('notifications.support.message_line'))
            ->line($this->data->message)
            ->line(Lang::get('notifications.support.respond'))
            ->action(Lang::get('notifications.support.action'), url('/dashboard/admin/reports'))
            ->line(Lang::get('notifications.support.thanks'));
    }
}
