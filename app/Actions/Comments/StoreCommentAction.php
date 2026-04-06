<?php

declare(strict_types=1);

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class StoreCommentAction
{
    public function exec(User $user, Post $post, array $data): Comment
    {
        return DB::transaction(function () use ($user, $post, $data) {
            $comment = Comment::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'parent_id' => $data['parent_id'] ?? null,
                'content' => $data['content'],
                'status' => 'visible',
            ]);

            $post->timestamps = false;
            $post->increment('comments_count');

            return $comment;
        });
    }
}
