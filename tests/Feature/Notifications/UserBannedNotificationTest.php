<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\Reports\UserBannedNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

it('sends the user banned notification via mail', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new UserBannedNotification(Carbon::parse('2026-01-01 10:00:00'), 'Spam');

    expect($notification->via($user))->toBe(['mail']);
});

it('builds the user banned mail message', function () {
    $user = User::factory()->withProfile()->create(['name' => 'Ana']);
    $bannedUntil = Carbon::parse('2026-01-01 10:00:00');

    $notification = new UserBannedNotification($bannedUntil, 'Spam');
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->level)->toBe('error')
        ->and($mail->subject)->toBe(__('notifications.report.banned.subject', ['app' => config('app.name')]))
        ->and($mail->greeting)->toBe(__('notifications.report.banned.greeting', ['name' => 'Ana']))
        ->and($mail->actionText)->toBe(__('notifications.report.banned.action'))
        ->and($mail->actionUrl)->toBe(url('/termos'));
});
