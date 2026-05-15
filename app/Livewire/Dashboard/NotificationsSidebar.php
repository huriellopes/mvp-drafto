<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationsSidebar extends Component
{
    public bool $show = false;

    public int $amount = 20;

    #[On('toggleNotifications')]
    public function toggle()
    {
        $this->show = !$this->show;

        if ($this->show) {
            $this->dispatch('notification-updated');
        }
    }

    #[Computed]
    public function notifications()
    {
        return auth()->user()
            ->notifications()
            ->latest()
            ->take($this->amount)
            ->get();
    }

    public function loadMore(): void
    {
        $this->amount += 20;
    }

    /**
     * Marca como lida e redireciona para o link da ação
     */
    public function readAndRedirect($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-updated');

            return $this->redirect($notification->data['link'], navigate: true);
        }
    }

    public function markAsRead($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
        $this->dispatch('notification-updated');
    }

    public function delete($id)
    {
        auth()->user()->notifications()->where('id', $id)->delete();
        $this->dispatch('notification-updated');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('notification-updated');
    }

    public function deleteAll()
    {
        auth()->user()->notifications()->delete();
        $this->dispatch('notification-updated');
    }

    public function render()
    {
        return view('livewire.dashboard.notifications-sidebar');
    }
}
