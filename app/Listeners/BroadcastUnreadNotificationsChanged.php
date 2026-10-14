<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\Notifications\UnreadNotificationsChanged;
use App\Models\User;
use Illuminate\Notifications\Events\NotificationSent;

/**
 * Every database notification stored for a user pushes a refresh signal to
 * that user's bell — a single hook instead of adding the "broadcast" channel
 * to each Notification class.
 */
final class BroadcastUnreadNotificationsChanged
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel !== 'database' || !$event->notifiable instanceof User) {
            return;
        }

        broadcast(new UnreadNotificationsChanged($event->notifiable->getKey()));
    }
}
