<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use App\Actions\Users\ToggleFollowAction;
use App\Models\User;
use Livewire\Component;

class FollowButton extends Component
{
    public User $user;

    public bool $compact = false;

    public bool $iconOnly = false;

    public bool $isFollowing = false;

    public function mount(): void
    {
        $this->checkStatus();
    }

    public function toggle()
    {
        if (auth()->guest()) {
            return $this->redirect(route('login'), navigate: true);
        }

        if (auth()->id() === $this->user->id) {
            return;
        }

        app(ToggleFollowAction::class)->exec(auth()->user(), $this->user);
        $this->checkStatus();

        $this->dispatch('follow-updated');
    }

    public function render()
    {
        return view('livewire.actions.follow-button');
    }

    private function checkStatus(): void
    {
        $this->isFollowing = $this->user->is_followed_by_auth_user ?? (auth()->check() && auth()->user()->isFollowing($this->user));
    }
}
