<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('sends the welcome notification via mail', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new WelcomeNotification('secret-pass');

    expect($notification->via($user))->toBe(['mail']);
});

it('builds the welcome mail message', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new WelcomeNotification('secret-pass');
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Bem-vindo à Drafto!')
        ->and($mail->view)->toBe('emails.auth.welcome')
        ->and($mail->viewData['user'])->toBe($user)
        ->and($mail->viewData['password'])->toBe('secret-pass')
        ->and($mail->viewData['url'])->toBe(route('login'))
        ->and($mail->viewData['verificationUrl'])->toBeString();
});
