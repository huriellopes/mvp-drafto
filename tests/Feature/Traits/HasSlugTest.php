<?php

declare(strict_types=1);

namespace Tests\Feature\Traits;

use App\Models\Post;
use App\Models\PostCategory;

it('generates a slug from the title source field on creation', function () {
    $post = Post::factory()->create(['title' => 'Hello World Article', 'slug' => null]);

    expect($post->slug)->toBe('hello-world-article');
});

it('suffixes -1 and -2 when slugs collide for the title source field', function () {
    $first = Post::factory()->create(['title' => 'Repeated Title', 'slug' => null]);
    $second = Post::factory()->create(['title' => 'Repeated Title', 'slug' => null]);
    $third = Post::factory()->create(['title' => 'Repeated Title', 'slug' => null]);

    expect($first->slug)->toBe('repeated-title')
        ->and($second->slug)->toBe('repeated-title-1')
        ->and($third->slug)->toBe('repeated-title-2');
});

it('uses the name source field for models that have a name column', function () {
    $category = PostCategory::factory()->create(['name' => 'Tech News', 'slug' => null]);

    expect($category->slug)->toBe('tech-news');
});

it('regenerates the slug on update when the source field is dirty and slug is not', function () {
    $post = Post::factory()->create(['title' => 'Original Heading', 'slug' => null]);

    $post->update(['title' => 'Updated Heading']);

    expect($post->fresh()->slug)->toBe('updated-heading');
});

it('does not regenerate the slug on update when the slug was explicitly set', function () {
    $post = Post::factory()->create(['title' => 'Some Heading', 'slug' => null]);

    $post->update(['title' => 'Another Heading', 'slug' => 'manual-slug']);

    expect($post->fresh()->slug)->toBe('manual-slug');
});

it('does not regenerate the slug when the source field is unchanged', function () {
    $post = Post::factory()->create(['title' => 'Stable Heading', 'slug' => null]);
    $originalSlug = $post->slug;

    $post->update(['excerpt' => 'just an excerpt change']);

    expect($post->fresh()->slug)->toBe($originalSlug);
});

it('finds a model by its slug and exposes slug as the route key', function () {
    $post = Post::factory()->create(['title' => 'Findable Post', 'slug' => null]);

    expect(Post::findBySlug('findable-post')->is($post))->toBeTrue()
        ->and($post->getRouteKeyName())->toBe('slug');
});
