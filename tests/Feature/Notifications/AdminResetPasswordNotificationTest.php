<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\Users\AdminResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('sends the admin reset password notification via mail', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new AdminResetPasswordNotification('new-pass');

    expect($notification->via($user))->toBe(['mail']);
});

it('builds the admin reset password mail message', function () {
    $user = User::factory()->withProfile()->create(['name' => 'Beatriz']);

    $notification = new AdminResetPasswordNotification('new-pass');
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe(__('notifications.admin.password_reset.subject'))
        ->and($mail->greeting)->toBe(__('notifications.admin.password_reset.greeting', ['name' => 'Beatriz']))
        ->and($mail->actionText)->toBe(__('notifications.admin.password_reset.action'))
        ->and($mail->actionUrl)->toBe(route('login'))
        ->and($mail->introLines)->toContain(__('notifications.admin.password_reset.line2', ['password' => 'new-pass']));
});
