<?php

declare(strict_types=1);

namespace App\Events\Notifications;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Tells the notification bell to refresh its unread counter. Replaces the
 * 60s polling: the bell only hits the server when something actually changed.
 *
 * Queued (ShouldBroadcast) so sending a notification never waits on the
 * websocket server, and a Reverb outage is retried by the queue.
 */
final class UnreadNotificationsChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly int|string $userId,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("App.Models.User.{$this->userId}");
    }

    public function broadcastAs(): string
    {
        return 'notifications.unread-changed';
    }

    /**
     * @return array<string, never>
     */
    public function broadcastWith(): array
    {
        return [];
    }
}
