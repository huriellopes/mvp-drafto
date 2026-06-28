<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessPostViewJob;
use App\Models\Post;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

function makePostViewJob(int $postId, ?int $userId = null, string $session = 'sess-1', string $ipHash = 'ip-hash-1', string $ua = 'Mozilla'): ProcessPostViewJob
{
    return new ProcessPostViewJob($postId, $userId, $session, $ipHash, $ua);
}

it('records a post view and increments the redis buffer', function (): void {
    Redis::shouldReceive('incr')->once()->withAnyArgs();

    $post = Post::factory()->create();

    app()->call([makePostViewJob($post->id, $post->user_id), 'handle']);

    expect(DB::table('post_views')->where('post_id', $post->id)->count())->toBe(1);

    $row = DB::table('post_views')->where('post_id', $post->id)->first();

    expect($row->user_id)->toBe($post->user_id)
        ->and($row->session_id)->toBe('sess-1')
        ->and($row->ip_hash)->toBe('ip-hash-1')
        ->and($row->viewed_at)->not->toBeNull();
});

it('throttles a repeated view within the last hour (same session)', function (): void {
    Redis::shouldReceive('incr')->never();

    $post = Post::factory()->create();

    DB::table('post_views')->insert([
        'post_id' => $post->id,
        'user_id' => null,
        'session_id' => 'sess-1',
        'ip_hash' => 'different-ip',
        'user_agent' => 'old',
        'viewed_at' => now()->subMinutes(10),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app()->call([makePostViewJob($post->id), 'handle']);

    expect(DB::table('post_views')->where('post_id', $post->id)->count())->toBe(1);
});

it('does not throttle a view older than one hour', function (): void {
    Redis::shouldReceive('incr')->once()->withAnyArgs();

    $post = Post::factory()->create();

    DB::table('post_views')->insert([
        'post_id' => $post->id,
        'user_id' => null,
        'session_id' => 'sess-1',
        'ip_hash' => 'ip-hash-1',
        'user_agent' => 'old',
        'viewed_at' => now()->subHours(2),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app()->call([makePostViewJob($post->id), 'handle']);

    expect(DB::table('post_views')->where('post_id', $post->id)->count())->toBe(2);
});

it('truncates the user agent to 255 characters', function (): void {
    Redis::shouldReceive('incr')->once()->withAnyArgs();

    $post = Post::factory()->create();

    app()->call([makePostViewJob($post->id, null, 'sess-1', 'ip-hash-1', str_repeat('x', 500)), 'handle']);

    $row = DB::table('post_views')->where('post_id', $post->id)->first();

    expect(mb_strlen((string) $row->user_agent))->toBe(255);
});

it('still records the view when redis incr throws', function (): void {
    Redis::shouldReceive('incr')->once()->andThrow(new Exception('redis down'));

    $post = Post::factory()->create();

    app()->call([makePostViewJob($post->id), 'handle']);

    expect(DB::table('post_views')->where('post_id', $post->id)->count())->toBe(1);
});
