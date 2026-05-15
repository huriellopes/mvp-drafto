<?php

declare(strict_types=1);

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Support\Facades\DB;

final class ToggleCommentLikeAction
{
    public function exec(?User $user, Comment $comment, ?string $ipAddress = null): bool
    {
        return DB::transaction(function () use ($user, $comment, $ipAddress) {
            $query = DB::table('comment_likes')
                ->where('comment_id', $comment->id);

            if ($user) {
                $query->where('user_id', $user->id);
            } else {
                $query->whereNull('user_id')->where('ip_address', $ipAddress);
            }

            $existing = $query->first();

            if ($existing) {
                $query->delete();

                return false;
            }

            DB::table('comment_likes')->insert([
                'comment_id' => $comment->id,
                'user_id' => $user?->id,
                'ip_address' => $user ? null : $ipAddress,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($user && $comment->user && $comment->user_id !== $user->id) {
                $comment->user->notify(new SocialInteractionNotification('like_comment', $comment, $user));
            }

            return true;
        });
    }
}
