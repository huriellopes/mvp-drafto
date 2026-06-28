<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\SupportStatusEnum;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SupportResponseNotification;
use Illuminate\Notifications\Messages\MailMessage;

function makeSupport(): Support
{
    $user = User::factory()->withProfile()->create();

    return Support::create([
        'user_id' => $user->id,
        'subject' => 'Dúvida',
        'message' => 'Mensagem do usuário',
        'status' => SupportStatusEnum::RESOLVED,
        'admin_response' => 'Resposta da equipe',
    ]);
}

it('sends the support response notification via mail and database', function () {
    $user = User::factory()->withProfile()->create();
    $support = makeSupport();

    $notification = new SupportResponseNotification($support);

    expect($notification->via($user))->toBe(['mail', 'database']);
});

it('builds the support response mail message', function () {
    $user = User::factory()->withProfile()->create(['name' => 'Pedro']);
    $support = makeSupport();

    $notification = new SupportResponseNotification($support);
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe(__('notifications.support.response.subject', ['subject' => 'Dúvida']))
        ->and($mail->greeting)->toBe(__('notifications.support.response.greeting', ['name' => 'Pedro']))
        ->and($mail->actionText)->toBe(__('notifications.support.response.action'))
        ->and($mail->actionUrl)->toBe(url('/dashboard/suporte'));
});

it('exposes the support response payload as an array', function () {
    $user = User::factory()->withProfile()->create();
    $support = makeSupport();

    $notification = new SupportResponseNotification($support);

    expect($notification->toArray($user))->toBe([
        'support_id' => $support->id,
        'status' => $support->status->value,
        'causer_name' => 'Equipe Drafto',
        'message' => 'notifications.support.response.database_message',
        'link' => '/dashboard/suporte',
    ]);
});
