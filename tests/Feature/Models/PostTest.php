<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Spatie\Sitemap\Tags\Url;

it('builds a sitemap tag from the slug', function () {
    $post = Post::factory()->published()->create();

    $tag = $post->toSitemapTag();

    expect($tag)->toBeInstanceOf(Url::class)
        ->and($tag->url)->toContain($post->slug);
});

it('scopes published posts only', function () {
    Post::factory()->published()->create();
    Post::factory()->draft()->create();

    $results = Post::published()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->status)->toBe(PostStatusEnum::PUBLISHED);
});

it('scopes scheduled posts only', function () {
    Post::factory()->create(['status' => PostStatusEnum::SCHEDULED]);
    Post::factory()->draft()->create();

    expect(Post::scheduled()->get())->toHaveCount(1);
});

it('scopes public posts only', function () {
    Post::factory()->public()->create();
    Post::factory()->unlisted()->create();

    $results = Post::public()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->visibility)->toBe(PostVisibilityEnum::PUBLIC);
});

it('scopes articles and regular posts', function () {
    Post::factory()->article()->create();
    Post::factory()->post()->create();

    expect(Post::articles()->get())->toHaveCount(1)
        ->and(Post::articles()->first()->type)->toBe(PostTypeEnum::ARTICLE)
        ->and(Post::regularPosts()->get())->toHaveCount(1)
        ->and(Post::regularPosts()->first()->type)->toBe(PostTypeEnum::POST);
});

it('reports published status correctly', function () {
    expect(Post::factory()->published()->create()->isPublished())->toBeTrue()
        ->and(Post::factory()->draft()->create()->isPublished())->toBeFalse();
});

it('reports scheduled status correctly', function () {
    expect(Post::factory()->create(['status' => PostStatusEnum::SCHEDULED])->isScheduled())->toBeTrue()
        ->and(Post::factory()->draft()->create()->isScheduled())->toBeFalse();
});

it('reports article and regular post types correctly', function () {
    expect(Post::factory()->article()->create()->isArticle())->toBeTrue()
        ->and(Post::factory()->post()->create()->isArticle())->toBeFalse()
        ->and(Post::factory()->post()->create()->isRegularPost())->toBeTrue()
        ->and(Post::factory()->article()->create()->isRegularPost())->toBeFalse();
});

it('returns null cover image url when no path is set', function () {
    $post = Post::factory()->create(['cover_image_path' => null]);

    expect($post->cover_image_url)->toBeNull();
});

it('returns the raw url when cover image path is an http url', function () {
    $post = Post::factory()->create(['cover_image_path' => 'https://cdn.example.com/x.jpg']);

    expect($post->cover_image_url)->toBe('https://cdn.example.com/x.jpg');
});

it('builds a storage asset url for a local cover image path', function () {
    $post = Post::factory()->create(['cover_image_path' => 'covers/x.jpg']);

    expect($post->cover_image_url)->toContain('storage/covers/x.jpg');
});

it('recalculates reading time when content changes on save', function () {
    $post = Post::factory()->create();

    $post->content = str_repeat('word ', 400);
    $post->save();

    expect($post->reading_time)->toBeGreaterThan(0);
});

it('belongs to author, user and category', function () {
    $user = User::factory()->writer()->create();
    $category = PostCategory::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id, 'category_id' => $category->id]);

    expect($post->author->id)->toBe($user->id)
        ->and($post->user->id)->toBe($user->id)
        ->and($post->category->id)->toBe($category->id);
});
