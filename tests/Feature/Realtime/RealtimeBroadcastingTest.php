<?php

declare(strict_types=1);

namespace Tests\Feature\Realtime;

use App\Events\Exports\ExportFailed;
use App\Events\Exports\ExportReady;
use App\Events\Notifications\UnreadNotificationsChanged;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;

it('broadcasts the unread change when a database notification is stored', function () {
    Event::fake([UnreadNotificationsChanged::class]);
    $user = User::factory()->create();

    event(new NotificationSent($user, mock(SocialInteractionNotification::class), 'database'));

    Event::assertDispatched(
        UnreadNotificationsChanged::class,
        fn (UnreadNotificationsChanged $event): bool => $event->userId === $user->id,
    );
});

it('ignores non-database channels', function () {
    Event::fake([UnreadNotificationsChanged::class]);
    $user = User::factory()->create();

    event(new NotificationSent($user, mock(SocialInteractionNotification::class), 'mail'));

    Event::assertNotDispatched(UnreadNotificationsChanged::class);
});

it('ignores notifiables that are not users', function () {
    Event::fake([UnreadNotificationsChanged::class]);

    event(new NotificationSent(new AnonymousNotifiable, mock(SocialInteractionNotification::class), 'database'));

    Event::assertNotDispatched(UnreadNotificationsChanged::class);
});

it('targets the owner private channel with a stable event name', function (object $event, string $name, array $payload) {
    expect($event->broadcastOn())->toBeInstanceOf(PrivateChannel::class)
        ->and($event->broadcastOn()->name)->toBe('private-App.Models.User.9')
        ->and($event->broadcastAs())->toBe($name)
        ->and($event->broadcastWith())->toBe($payload);
})->with([
    'export ready' => [fn () => new ExportReady(9, 'temp/exports/9/a.xlsx'), 'export.ready', ['path' => 'temp/exports/9/a.xlsx']],
    'export failed' => [fn () => new ExportFailed(9, 'temp/exports/9/a.xlsx'), 'export.failed', ['path' => 'temp/exports/9/a.xlsx']],
    'unread changed' => [fn () => new UnreadNotificationsChanged(9), 'notifications.unread-changed', []],
]);

it('authorizes only the owner on the user private channel', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $callback = Broadcast::driver()->getChannels()->get('App.Models.User.{id}');

    expect($callback($user, $user->id))->toBeTrue()
        ->and($callback($user, $other->id))->toBeFalse();
});
