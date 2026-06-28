<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

it('casts viewed_at to a datetime', function () {
    $view = PostView::factory()->create([
        'viewed_at' => '2026-01-01 10:00:00',
    ]);

    expect($view->viewed_at)->toBeInstanceOf(Carbon::class)
        ->and($view->viewed_at->toDateString())->toBe('2026-01-01');
});

it('belongs to a post', function () {
    $post = Post::factory()->published()->create();
    $view = PostView::factory()->forPost($post)->create();

    expect($view->post())->toBeInstanceOf(BelongsTo::class)
        ->and($view->post->id)->toBe($post->id);
});

it('belongs to a user and an aliased viewer', function () {
    $user = User::factory()->active()->create();
    $view = PostView::factory()->byUser($user)->create();

    expect($view->user())->toBeInstanceOf(BelongsTo::class)
        ->and($view->user->id)->toBe($user->id)
        ->and($view->viewer())->toBeInstanceOf(BelongsTo::class)
        ->and($view->viewer->id)->toBe($user->id);
});

it('allows anonymous views with no user', function () {
    $view = PostView::factory()->anonymous()->create();

    expect($view->user_id)->toBeNull()
        ->and($view->session_id)->not->toBeNull();
});
