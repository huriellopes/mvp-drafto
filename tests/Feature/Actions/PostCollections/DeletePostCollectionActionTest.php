<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\PostCollections;

use App\Actions\PostCollections\DeletePostCollectionAction;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\User;

beforeEach(function () {
    $this->action = app(DeletePostCollectionAction::class);
});

it('deletes the collection and detaches its posts without deleting them', function () {
    $user = User::factory()->writer()->create();
    $collection = PostCollection::factory()->create(['user_id' => $user->id]);
    $post = Post::factory()->published()->create(['user_id' => $user->id]);

    $collection->posts()->attach($post->id);

    $this->action->exec($collection);

    $this->assertModelMissing($collection);
    $this->assertModelExists($post);
    $this->assertDatabaseMissing('post_collection_post', ['post_collection_id' => $collection->id]);
});
