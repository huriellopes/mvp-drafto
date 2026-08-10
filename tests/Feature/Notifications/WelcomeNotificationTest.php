<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('sends the welcome notification via mail', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new WelcomeNotification;

    expect($notification->via($user))->toBe(['mail']);
});

it('builds the welcome mail message', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new WelcomeNotification;
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('Bem-vindo à Drafto!')
        ->and($mail->view)->toBe('emails.auth.welcome')
        ->and($mail->viewData['user'])->toBe($user)
        // Segurança: a senha não é mais enviada por e-mail — nunca deve
        // aparecer no viewData da notificação de boas-vindas.
        ->and($mail->viewData)->not->toHaveKey('password')
        ->and($mail->viewData['url'])->toBe(route('login'))
        ->and($mail->viewData['verificationUrl'])->toBeString();
});
