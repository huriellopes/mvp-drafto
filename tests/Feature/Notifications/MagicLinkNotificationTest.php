<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\Auth\MagicLinkNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('sends the magic link via mail', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new MagicLinkNotification('test-token');

    expect($notification->via($user))->toBe(['mail']);
});

it('builds the magic link mail message', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new MagicLinkNotification('test-token');
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe(__('mail.auth.magic_link.subject'))
        ->and($mail->view)->toBe('emails.auth.magic-link')
        ->and($mail->viewData['user'])->toBe($user)
        ->and($mail->viewData['url'])->toContain('test-token');
});
