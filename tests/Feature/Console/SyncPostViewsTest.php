<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Post;
use Illuminate\Support\Facades\Redis;

it('increments post view counters from the redis buffer', function () {
    $post = Post::factory()->create(['views_count' => 0]);

    $prefix = (string) config('database.redis.options.prefix');
    $key = $prefix . 'post_views_buffer:' . $post->id;

    Redis::shouldReceive('keys')
        ->once()
        ->with('post_views_buffer:*')
        ->andReturn([$key]);

    Redis::shouldReceive('getdel')
        ->once()
        ->with($key)
        ->andReturn(7);

    $this->artisan('drafto:sync-views')
        ->assertExitCode(0);

    expect($post->fresh()->views_count)->toBe(7);
});

it('reports nothing to sync when the buffer is empty', function () {
    Redis::shouldReceive('keys')
        ->once()
        ->with('post_views_buffer:*')
        ->andReturn([]);

    $this->artisan('drafto:sync-views')
        ->expectsOutputToContain('No buffered views to sync.')
        ->assertExitCode(0);
});
