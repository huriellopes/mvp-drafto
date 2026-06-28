<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Models\Post;
use App\Observers\PostObserver;
use Illuminate\Support\Facades\Cache;

it('clears the cache on the deleted event', function () {
    $post = Post::factory()->published()->create();
    Cache::put("post_show_{$post->slug}", 'cached', 60);

    $post->delete();

    expect(Cache::has("post_show_{$post->slug}"))->toBeFalse();
});

it('clears the cache on the restored hook', function () {
    $post = Post::factory()->published()->create();
    Cache::put("post_show_{$post->slug}", 'cached', 60);

    (new PostObserver())->restored($post);

    expect(Cache::has("post_show_{$post->slug}"))->toBeFalse();
});

it('clears the cache on the forceDeleted hook', function () {
    $post = Post::factory()->published()->create();
    Cache::put("post_show_{$post->slug}", 'cached', 60);

    (new PostObserver())->forceDeleted($post);

    expect(Cache::has("post_show_{$post->slug}"))->toBeFalse();
});
