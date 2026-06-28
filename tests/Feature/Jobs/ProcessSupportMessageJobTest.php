<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\DTOs\SupportContactData;
use App\Jobs\ProcessSupportMessageJob;
use App\Notifications\SupportMessageReceivedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

beforeEach(function (): void {
    Notification::fake();
});

it('sends the support notification to the configured support email', function (): void {
    config()->set('support.email', 'support@drafto.pro');

    $data = new SupportContactData(
        name: 'John Doe',
        email: 'john@example.com',
        subject: 'Need help',
        message: 'Something is broken.',
    );

    app()->call([new ProcessSupportMessageJob($data), 'handle']);

    Notification::assertSentOnDemand(
        SupportMessageReceivedNotification::class,
        function (SupportMessageReceivedNotification $notification, array $channels, AnonymousNotifiable $notifiable): bool {
            return $notifiable->routeNotificationFor('mail') === 'support@drafto.pro';
        },
    );
});

it('sends exactly one support notification', function (): void {
    $data = new SupportContactData(
        name: 'Jane',
        email: 'jane@example.com',
        subject: 'Hi',
        message: 'Hello there.',
    );

    app()->call([new ProcessSupportMessageJob($data), 'handle']);

    Notification::assertCount(1);
});
