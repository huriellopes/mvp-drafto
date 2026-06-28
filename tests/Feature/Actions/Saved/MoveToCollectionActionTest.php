<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Saved;

use App\Actions\Saved\MoveToCollectionAction;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->action = app(MoveToCollectionAction::class);
});

it('moves a saved post into a collection', function () {
    $user = User::factory()->create();
    $collection = $user->collections()->create(['name' => 'Box', 'slug' => 'box']);
    $post = Post::factory()->published()->create();
    $user->savedPosts()->attach($post->id);

    $this->action->exec($user, $post->id, $collection->id);

    $pivot = $user->savedPosts()->where('posts.id', $post->id)->first()->pivot;
    expect($pivot->collection_id)->toBe($collection->id);
});

it('removes a saved post from any collection when passing null', function () {
    $user = User::factory()->create();
    $collection = $user->collections()->create(['name' => 'Box', 'slug' => 'box']);
    $post = Post::factory()->published()->create();
    $user->savedPosts()->attach($post->id, ['collection_id' => $collection->id]);

    $this->action->exec($user, $post->id, null);

    $pivot = $user->savedPosts()->where('posts.id', $post->id)->first()->pivot;
    expect($pivot->collection_id)->toBeNull();
});
