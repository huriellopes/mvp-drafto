<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\Models\Post;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Support\Facades\DB;

final class ToggleLikeAction
{
    public function exec(?User $user, Post $post, ?string $ipAddress = null): bool
    {
        return DB::transaction(function () use ($user, $post, $ipAddress) {
            $query = DB::table('post_likes')
                ->where('post_id', $post->id);

            if ($user) {
                $query->where('user_id', $user->id);
            } else {
                $query->whereNull('user_id')->where('ip_address', $ipAddress);
            }

            $existing = $query->first();

            if ($existing) {
                $query->delete();
                $post->timestamps = false;
                $post->decrement('likes_count');

                return false;
            }

            DB::table('post_likes')->insert([
                'post_id' => $post->id,
                'user_id' => $user?->id,
                'ip_address' => $user ? null : $ipAddress,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $post->timestamps = false;
            $post->increment('likes_count');

            if ($user && $post->user_id !== $user->id) {
                $post->author->notify(new SocialInteractionNotification('like_post', $post, $user));
            }

            return true;
        });
    }
}
