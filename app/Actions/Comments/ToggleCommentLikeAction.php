<?php

declare(strict_types=1);

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Support\Facades\DB;

final class ToggleCommentLikeAction
{
    public function exec(User $user, Comment $comment): bool
    {
        return DB::transaction(function () use ($user, $comment) {
            $result = $user->likedComments()->toggle($comment->id);
            $isAttached = count($result['attached']) > 0;

            if ($isAttached && $comment->user_id !== $user->id) {
                $comment->user->notify(new SocialInteractionNotification('like_comment', $comment, $user));
            }

            return $isAttached;
        });
    }
}
