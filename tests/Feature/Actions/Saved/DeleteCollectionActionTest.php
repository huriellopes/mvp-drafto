<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Saved;

use App\Actions\Saved\DeleteCollectionAction;
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\User;

beforeEach(function () {
    $this->action = app(DeleteCollectionAction::class);
});

it('deletes the collection and nulls the collection_id on saved posts', function () {
    $user = User::factory()->create();
    $collection = $user->collections()->create(['name' => 'Box', 'slug' => 'box']);
    $post = Post::factory()->published()->create();

    $saved = SavedPost::create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'collection_id' => $collection->id,
    ]);

    // Segurança (IDOR): a Action agora reconfirma a posse via CollectionPolicy
    // internamente, então precisa de um usuário autenticado no contexto.
    $this->actingAs($user);

    $this->action->exec($collection);

    $this->assertModelMissing($collection);
    expect($saved->fresh()->collection_id)->toBeNull();
});

it('does not delete a collection belonging to another user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $collection = $owner->collections()->create(['name' => 'Box', 'slug' => 'box']);

    $this->actingAs($intruder);

    $this->action->exec($collection);

    $this->assertModelExists($collection);
});
