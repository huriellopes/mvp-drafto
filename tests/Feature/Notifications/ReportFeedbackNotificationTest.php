<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Report;
use App\Models\User;
use App\Notifications\Reports\ReportFeedbackNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('sends the report feedback notification via mail and database', function () {
    $user = User::factory()->withProfile()->create();
    $report = Report::factory()->reviewed()->create(['admin_feedback' => 'Tudo certo']);

    $notification = new ReportFeedbackNotification($report);

    expect($notification->via($user))->toBe(['mail', 'database']);
});

it('builds the report feedback mail message', function () {
    $user = User::factory()->withProfile()->create(['name' => 'João']);
    $report = Report::factory()->reviewed()->create(['admin_feedback' => 'Tudo certo']);

    $notification = new ReportFeedbackNotification($report);
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe(__('notifications.report.feedback.subject', ['app' => config('app.name')]))
        ->and($mail->greeting)->toBe(__('notifications.report.feedback.greeting', ['name' => 'João']))
        ->and($mail->actionText)->toBe(__('notifications.report.feedback.action'))
        ->and($mail->actionUrl)->toBe(url('/diretrizes'));
});

it('exposes the report feedback payload as an array', function () {
    $user = User::factory()->withProfile()->create();
    $report = Report::factory()->reviewed()->create(['admin_feedback' => 'Tudo certo']);

    $notification = new ReportFeedbackNotification($report);

    expect($notification->toArray($user))->toBe([
        'report_id' => $report->id,
        'status' => $report->status->value,
        'message' => 'notifications.report.feedback.database_message',
    ]);
});
