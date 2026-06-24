<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Notifications\Posts\PostPublishedNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublishScheduledPostsAction
{
    public function exec(): int
    {
        $scheduledPosts = Post::query()
            ->where('status', PostStatusEnum::SCHEDULED)
            ->where('published_at', '<=', now())
            ->with('author')
            ->get();

        $publishedCount = 0;

        foreach ($scheduledPosts as $post) {
            try {
                DB::transaction(function () use ($post, &$publishedCount) {
                    $post->update([
                        'status' => PostStatusEnum::PUBLISHED,
                    ]);

                    $post->author->notify(new PostPublishedNotification($post));

                    $publishedCount++;
                });
            } catch (Exception $e) {
                Log::error("Erro ao publicar post agendado [ID: {$post->id}]: " . $e->getMessage());
            }
        }

        return $publishedCount;
    }
}
