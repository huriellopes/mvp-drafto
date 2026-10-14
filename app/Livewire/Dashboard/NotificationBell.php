<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Override;

/**
 * Unread counter refreshed by push (Reverb) instead of a 60s poll: the bell
 * only re-renders when a notification is actually stored for the user. While
 * Echo is disconnected, the view falls back to polling (`realtimeFallback`).
 */
class NotificationBell extends Component
{
    #[On('notification-updated')]
    public function refresh(): void
    {
        // Apenas para disparar re-render
    }

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function render()
    {
        return view('livewire.dashboard.notification-bell');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function getListeners(): array
    {
        return [
            'echo-private:App.Models.User.' . auth()->id() . ',.notifications.unread-changed' => 'refresh',
        ];
    }
}
