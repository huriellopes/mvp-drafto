<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('builds the verify email mail message', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new VerifyEmailNotification();
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Confirme seu e-mail na Drafto')
        ->and($mail->view)->toBe('emails.auth.verify-email')
        ->and($mail->viewData['user'])->toBe($user)
        ->and($mail->viewData['url'])->toBeString()
        ->and($mail->viewData['url'])->not->toBeEmpty();
});
