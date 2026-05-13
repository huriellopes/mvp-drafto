<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\Models\Post;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Support\Facades\DB;

final class ToggleLikeAction
{
    public function exec(User $user, Post $post): bool
    {
        return DB::transaction(function () use ($user, $post) {
            $result = $user->likedPosts()->toggle($post->id);
            $isAttached = count($result['attached']) > 0;

            $post->timestamps = false;
            $isAttached ? $post->increment('likes_count') : $post->decrement('likes_count');

            if ($isAttached && $post->user_id !== $user->id) {
                $post->author->notify(new SocialInteractionNotification('like_post', $post, $user));
            }

            return $isAttached;
        });
    }
}
