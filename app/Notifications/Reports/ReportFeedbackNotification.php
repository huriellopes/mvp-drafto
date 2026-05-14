<?php

declare(strict_types=1);

namespace App\Notifications\Reports;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

final class ReportFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Report $report,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(Lang::get('notifications.report.feedback.subject', ['app' => config('app.name')]))
            ->greeting(Lang::get('notifications.report.feedback.greeting', ['name' => $notifiable->name]))
            ->line(Lang::get('notifications.report.feedback.body', [
                'type' => class_basename($this->report->reportable_type)
            ]))
            ->line(Lang::get('notifications.report.feedback.status', [
                'status' => $this->report->status->label()
            ]))
            ->line(Lang::get('notifications.report.feedback.admin_feedback', [
                'feedback' => $this->report->admin_feedback
            ]))
            ->line(Lang::get('notifications.report.feedback.thanks'))
            ->action(Lang::get('notifications.report.feedback.action'), url('/diretrizes'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'status' => $this->report->status->value,
            'message' => Lang::get('notifications.report.feedback.database_message'),
        ];
    }
}
