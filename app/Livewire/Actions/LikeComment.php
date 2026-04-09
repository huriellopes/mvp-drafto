<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use App\Actions\Comments\ToggleCommentLikeAction;
use App\Models\Comment;
use Livewire\Component;

class LikeComment extends Component
{
    public Comment $comment;

    public function toggle()
    {
        if (auth()->guest()) {
            return $this->redirect(route('login'), navigate: true);
        }

        app(ToggleCommentLikeAction::class)->exec(auth()->user(), $this->comment);
    }

    public function render()
    {
        $isLiked = auth()->check() && auth()->user()->likedComments()->where('comment_id', $this->comment->id)->exists();
        $likesCount = $this->comment->likedByUsers()->count();

        return view('livewire.actions.like-comment', [
            'isLiked' => $isLiked,
            'likesCount' => $likesCount,
        ]);
    }
}
