<?php

declare(strict_types=1);

namespace App\Events\Exports;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Pushed to the owner's private channel when a background file could not be
 * generated, so the dashboard stops showing the "generating" state.
 */
final class ExportFailed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly int|string $userId,
        public readonly string $path,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("App.Models.User.{$this->userId}");
    }

    public function broadcastAs(): string
    {
        return 'export.failed';
    }

    /**
     * @return array{path: string}
     */
    public function broadcastWith(): array
    {
        return ['path' => $this->path];
    }
}
