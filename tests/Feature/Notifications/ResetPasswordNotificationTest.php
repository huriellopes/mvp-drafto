<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('builds the reset password mail message', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new ResetPasswordNotification('reset-token');
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Recuperação de senha na Drafto')
        ->and($mail->view)->toBe('emails.auth.reset-password')
        ->and($mail->viewData['user'])->toBe($user)
        ->and($mail->viewData['url'])->toContain('reset-token');
});
