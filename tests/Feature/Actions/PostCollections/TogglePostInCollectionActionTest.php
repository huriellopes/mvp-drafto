<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\PostCollections;

use App\Actions\PostCollections\TogglePostInCollectionAction;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\User;

beforeEach(function () {
    $this->action = app(TogglePostInCollectionAction::class);
});

it('attaches a post not yet in the collection', function () {
    $user = User::factory()->writer()->create();
    $collection = PostCollection::factory()->create(['user_id' => $user->id]);
    $post = Post::factory()->published()->create(['user_id' => $user->id]);

    $attached = $this->action->exec($collection, $post);

    expect($attached)->toBeTrue()
        ->and($collection->posts()->where('posts.id', $post->id)->exists())->toBeTrue();
});

it('detaches a post already in the collection', function () {
    $user = User::factory()->writer()->create();
    $collection = PostCollection::factory()->create(['user_id' => $user->id]);
    $post = Post::factory()->published()->create(['user_id' => $user->id]);
    $collection->posts()->attach($post->id);

    $attached = $this->action->exec($collection, $post);

    expect($attached)->toBeFalse()
        ->and($collection->posts()->where('posts.id', $post->id)->exists())->toBeFalse();
});
