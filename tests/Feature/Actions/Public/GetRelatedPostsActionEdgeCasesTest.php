<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Public;

use App\Actions\Public\GetRelatedPostsAction;
use App\DTOs\Public\RelatedPostsData;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->action = app(GetRelatedPostsAction::class);
});

it('forgets a corrupted cache value and recomputes the related posts', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->published()->public()->create(['category_id' => $category->id]);
    $related = Post::factory()->published()->public()->create(['category_id' => $category->id]);

    // Prime the cache with an invalid (non-array, non-collection) value so the
    // guard at lines 37-40 triggers a forget + recompute.
    $cacheKey = "post_related_{$post->id}_" . ($post->updated_at?->timestamp ?? 'static');
    Cache::put($cacheKey, 'corrupted-string-value', now()->addHour());

    $result = $this->action->exec($post);

    expect($result)->toBeInstanceOf(RelatedPostsData::class)
        ->and($result->posts->pluck('id')->all())->toContain($related->id);
});
