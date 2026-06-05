<?php

declare(strict_types=1);

namespace App\Notifications\Users;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AdminResetPasswordNotification extends Notification implements ShouldQueue
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
        return (new MailMessage())
            ->mailer('contact')
            ->subject(__('notifications.admin.password_reset.subject'))
            ->greeting(__('notifications.admin.password_reset.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.admin.password_reset.line1'))
            ->line(__('notifications.admin.password_reset.line2', ['password' => $this->password]))
            ->action(__('notifications.admin.password_reset.action'), route('login'))
            ->line(__('notifications.admin.password_reset.line3'))
            ->line(__('notifications.admin.password_reset.line4'));
    }
}
