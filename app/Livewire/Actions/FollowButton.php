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
            'isFollowing' => $isFollowing,
        ]);
    }
}
