<?php

namespace App\Livewire\Actions;

use App\Models\User;
use App\Actions\Users\ToggleFollowAction;
use Livewire\Component;

class FollowButton extends Component
{
    public User $user;

    public function toggle()
    {
        if (auth()->guest()) {
            return $this->redirect(route('login'), navigate: true);
        }

        app(ToggleFollowAction::class)->exec(auth()->user(), $this->user);
    }

    public function render()
    {
        $isFollowing = auth()->check() && auth()->user()->isFollowing($this->user);

        return view('livewire.actions.follow-button', [
            'isFollowing' => $isFollowing
        ]);
    }
}
