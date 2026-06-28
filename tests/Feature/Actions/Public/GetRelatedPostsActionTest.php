<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Public;

use App\Actions\Public\GetRelatedPostsAction;
use App\DTOs\Public\RelatedPostsData;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->action = app(GetRelatedPostsAction::class);
});

it('returns posts in the same category', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->published()->public()->create(['category_id' => $category->id]);
    $related = Post::factory()->published()->public()->create(['category_id' => $category->id]);
    Post::factory()->published()->public()->create(); // different category

    $result = $this->action->exec($post);

    expect($result)->toBeInstanceOf(RelatedPostsData::class)
        ->and($result->posts->pluck('id')->all())->toContain($related->id);
});

it('includes posts sharing tags even across categories', function () {
    $tag = Tag::factory()->create();

    $post = Post::factory()->published()->public()->create();
    $post->tags()->attach($tag->id);

    $tagged = Post::factory()->published()->public()->create();
    $tagged->tags()->attach($tag->id);

    $result = $this->action->exec($post->fresh());

    expect($result->posts->pluck('id')->all())->toContain($tagged->id);
});

it('never includes the source post itself', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->published()->public()->create(['category_id' => $category->id]);

    $result = $this->action->exec($post);

    expect($result->posts->pluck('id')->all())->not->toContain($post->id);
});

it('respects the requested limit', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->published()->public()->create(['category_id' => $category->id]);
    Post::factory()->published()->public()->count(5)->create(['category_id' => $category->id]);

    $result = $this->action->exec($post, limit: 2);

    expect($result->posts)->toHaveCount(2);
});
