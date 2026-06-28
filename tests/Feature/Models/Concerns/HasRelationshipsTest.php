<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use App\Models\Post;
use App\Models\User;

it('returns only published posts via publishedPosts', function () {
    $user = User::factory()->writer()->create();

    Post::factory()->published()->create(['user_id' => $user->id]);
    Post::factory()->draft()->create(['user_id' => $user->id]);

    expect($user->publishedPosts)->toHaveCount(1)
        ->and($user->posts)->toHaveCount(2);
});

it('exposes the profile has-one relationship', function () {
    $user = User::factory()->withProfile()->create();

    expect($user->profile)->not->toBeNull()
        ->and($user->profile->user_id)->toBe($user->id);
});

it('exposes liked and saved posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $user->likedPosts()->attach($post->id);
    $user->savedPosts()->attach($post->id);

    expect($user->likedPosts)->toHaveCount(1)
        ->and($user->savedPosts)->toHaveCount(1);
});
