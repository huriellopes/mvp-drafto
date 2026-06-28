<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

it('clears the cached post when it is updated', function () {
    $post = Post::factory()->published()->create();
    Cache::put("post_show_{$post->slug}", 'cached', 60);

    $post->update(['title' => 'A brand new title for the post']);

    expect(Cache::has("post_show_{$post->getOriginal('slug')}"))->toBeFalse();
});

it('clears the new slug cache and enters the wasChanged(slug) branch on a slug change', function () {
    $post = Post::factory()->published()->create(['slug' => 'old-slug']);

    Cache::put('post_show_completely-different-headline', 'cached', 60);

    // Changing the title regenerates the slug via HasSlug, so wasChanged('slug') is true
    // and the second Cache::forget branch in the observer is exercised.
    $post->update(['title' => 'Completely Different Headline']);

    expect($post->fresh()->slug)->toBe('completely-different-headline')
        ->and($post->wasChanged('slug'))->toBeTrue()
        ->and(Cache::has('post_show_completely-different-headline'))->toBeFalse();
});

it('only clears the current slug cache when the slug is unchanged on update', function () {
    $post = Post::factory()->published()->create(['slug' => 'stable-slug']);

    Cache::put("post_show_{$post->slug}", 'cached', 60);

    // Updating a non-source field keeps the slug, so wasChanged('slug') is false.
    $post->update(['excerpt' => 'updated excerpt only']);

    expect($post->wasChanged('slug'))->toBeFalse()
        ->and(Cache::has('post_show_stable-slug'))->toBeFalse();
});

it('clears the cached post when it is deleted', function () {
    $post = Post::factory()->published()->create();
    Cache::put("post_show_{$post->slug}", 'cached', 60);

    $post->delete();

    expect(Cache::has("post_show_{$post->slug}"))->toBeFalse();
});
