<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

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
}
