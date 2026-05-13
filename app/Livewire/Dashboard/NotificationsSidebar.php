<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationsSidebar extends Component
{
    public bool $show = false;

    #[On('toggleNotifications')]
    public function toggle()
    {
        $this->show = !$this->show;
    }

    /**
     * Marca como lida e redireciona para o link da ação
     */
    public function readAndRedirect($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();

            return $this->redirect($notification->data['link'], navigate: true);
        }
    }

    public function markAsRead($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
    }

    public function delete($id)
    {
        auth()->user()->notifications()->where('id', $id)->delete();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        return view('livewire.dashboard.notifications-sidebar', [
            'notifications' => auth()->user()->notifications()->latest()->take(30)->get(),
        ]);
    }
}
