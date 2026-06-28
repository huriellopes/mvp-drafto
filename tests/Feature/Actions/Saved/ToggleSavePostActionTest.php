<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Saved;

use App\Actions\Saved\ToggleSavePostAction;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->action = app(ToggleSavePostAction::class);
});

it('saves a post that was not yet saved', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $saved = $this->action->exec($user, $post);

    expect($saved)->toBeTrue()
        ->and($user->savedPosts()->where('posts.id', $post->id)->exists())->toBeTrue();
});

it('unsaves a post that was already saved', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $user->savedPosts()->attach($post->id);

    $saved = $this->action->exec($user, $post);

    expect($saved)->toBeFalse()
        ->and($user->savedPosts()->where('posts.id', $post->id)->exists())->toBeFalse();
});
