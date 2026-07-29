<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\DTOs\SupportContactData;
use App\Models\User;
use App\Notifications\SupportMessageReceivedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;

function supportContactData(): SupportContactData
{
    return new SupportContactData(
        name: 'Cliente',
        email: 'cliente@example.com',
        subject: 'Ajuda',
        message: 'Preciso de suporte',
    );
}

it('sends to mail for anonymous notifiables and database for users', function () {
    $notification = new SupportMessageReceivedNotification(supportContactData());

    $anonymous = new AnonymousNotifiable;
    $user = User::factory()->withProfile()->create();

    expect($notification->via($anonymous))->toBe(['mail'])
        ->and($notification->via($user))->toBe(['database']);
});

it('builds the support message received mail message', function () {
    $notification = new SupportMessageReceivedNotification(supportContactData());
    $mail = $notification->toMail(new AnonymousNotifiable);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe(__('notifications.support.subject', ['subject' => 'Ajuda']))
        ->and($mail->greeting)->toBe(__('notifications.support.greeting'))
        ->and($mail->actionText)->toBe(__('notifications.support.action'))
        ->and($mail->actionUrl)->toBe(url('/dashboard/admin/suporte'));
});

it('exposes the support message received payload as an array', function () {
    $notification = new SupportMessageReceivedNotification(supportContactData());

    expect($notification->toArray(new AnonymousNotifiable))->toBe([
        'type' => 'support_received',
        'causer_name' => 'Cliente',
        'message' => 'notifications.support.database_admin_message',
        'link' => '/dashboard/admin/suporte',
    ]);
});
