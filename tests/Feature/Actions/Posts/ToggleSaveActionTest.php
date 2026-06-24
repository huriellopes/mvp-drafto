<?php

declare(strict_types=1);

use App\Actions\Posts\ToggleSaveAction;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->action = new ToggleSaveAction();
});

it('saves a post when not previously saved', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $isSaved = $this->action->exec($user, $post);

    expect($isSaved)->toBeTrue()
        ->and($user->savedPosts()->where('post_id', $post->id)->exists())->toBeTrue();
});

it('unsaves a post when previously saved', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $user->savedPosts()->attach($post->id);

    $isSaved = $this->action->exec($user, $post);

    expect($isSaved)->toBeFalse()
        ->and($user->savedPosts()->where('post_id', $post->id)->exists())->toBeFalse();
});
