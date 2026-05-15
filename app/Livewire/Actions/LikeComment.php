<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use App\Actions\Comments\ToggleCommentLikeAction;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LikeComment extends Component
{
    public Comment $comment;

    public function toggle()
    {
        app(ToggleCommentLikeAction::class)->exec(auth()->user(), $this->comment, request()->ip());
    }

    public function render()
    {
        $ip = request()->ip();

        $isLiked = auth()->check()
            ? DB::table('comment_likes')->where('comment_id', $this->comment->id)->where('user_id', auth()->id())->exists()
            : DB::table('comment_likes')->where('comment_id', $this->comment->id)->whereNull('user_id')->where('ip_address', $ip)->exists();

        $likesCount = DB::table('comment_likes')->where('comment_id', $this->comment->id)->count();

        return view('livewire.actions.like-comment', [
            'isLiked' => $isLiked,
            'likesCount' => $likesCount,
        ]);
    }
}
