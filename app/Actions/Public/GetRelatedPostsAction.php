<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\DTOs\Public\RelatedPostsData;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class GetRelatedPostsAction
{
    public function exec(Post $post, int $limit = 3): RelatedPostsData
    {
        $cacheKey = "post_related_{$post->id}_" . ($post->updated_at?->timestamp ?? 'static');

        $relatedData = Cache::remember($cacheKey, now()->addHours(6), function () use ($post, $limit) {
            $tagIds = $post->tags->pluck('id');

            return Post::query()
                ->published()
                ->public()
                ->with(['author.profile', 'category'])
                ->where('id', '!=', $post->id)
                ->where(function ($query) use ($post, $tagIds) {
                    $query->where('category_id', $post->category_id)
                        ->when($tagIds->isNotEmpty(), function ($q) use ($tagIds) {
                            $q->orWhereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds));
                        });
                })
                ->latest()
                ->take($limit)
                ->get();
        });

        if (!is_array($relatedData) && !($relatedData instanceof Collection)) {
            Cache::forget($cacheKey);

            return $this->exec($post, $limit);
        }

        //        $posts = Post::hydrate($relatedData);

        return new RelatedPostsData(posts: $relatedData);
    }
}
