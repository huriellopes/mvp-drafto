<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\DTOs\Public\RelatedPostsData;
use App\Models\Post;

final class GetRelatedPostsAction
{
    public function exec(Post $post, int $limit = 3): RelatedPostsData
    {
        $tagIds = $post->tags->pluck('id');

        $related = Post::query()
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

        return new RelatedPostsData(posts: $related);
    }
}
