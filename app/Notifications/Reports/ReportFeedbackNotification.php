<?php

declare(strict_types=1);

namespace App\Notifications\Reports;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

final class ReportFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Report $report,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->mailer('support')
            ->subject(__('notifications.report.feedback.subject', ['app' => config('app.name')]))
            ->greeting(__('notifications.report.feedback.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.report.feedback.body', [
                'type' => class_basename($this->report->reportable_type),
            ]))
            ->line(__('notifications.report.feedback.status', [
                'status' => $this->report->status->label(),
            ]))
            ->line(__('notifications.report.feedback.admin_feedback', [
                'feedback' => $this->report->admin_feedback,
            ]))
            ->line(__('notifications.report.feedback.thanks'))
            ->action(__('notifications.report.feedback.action'), url('/diretrizes'));
    }

    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'status' => $this->report->status->value,
            'message' => 'notifications.report.feedback.database_message',
        ];
    }
}
