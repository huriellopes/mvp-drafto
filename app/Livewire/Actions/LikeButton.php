<?php

namespace App\Livewire\Actions;

use App\Models\Post;
use App\Actions\Posts\ToggleLikeAction;
use Illuminate\View\View;
use Livewire\Component;

class LikeButton extends Component
{
    public Post $post;

    public function toggle()
    {
        if (auth()->guest()) {
            return $this->redirect(route('login'), navigate: true);
        }

        app(ToggleLikeAction::class)
            ->exec(auth()->user(), $this->post);
        $this->post->refresh();
    }

    public function render() : View
    {
        $isLiked = auth()->check() && auth()->user()->likedPosts()->where('post_id', $this->post->id)->exists();

        return view('livewire.actions.like-button', ['isLiked' => $isLiked]);
    }
}
