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
            ->mailer('support')
            ->error()
            ->subject(__('notifications.report.banned.subject', ['app' => config('app.name')]))
            ->greeting(__('notifications.report.banned.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.report.banned.body'))
            ->line(__('notifications.report.banned.reason', ['reason' => $this->reason]))
            ->line(__('notifications.report.banned.until', [
                'date' => $this->bannedUntil->translatedFormat('d \d\e F \d\e Y \à\s H:i'),
            ]))
            ->line(__('notifications.report.banned.error_contact'))
            ->action(__('notifications.report.banned.action'), url('/termos'));
    }
}
