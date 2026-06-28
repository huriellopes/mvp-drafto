<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Saved;

use App\Livewire\Dashboard\Saved\SavedIndex;
use App\Models\Collection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

function edgeCollection(User $user, string $name = 'Reading'): Collection
{
    return Collection::create([
        'user_id' => $user->id,
        'name' => $name,
        'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
    ]);
}

/**
 * Covers updatedCollection() (line 59): resetPage when the active collection changes.
 */
it('resets pagination when the active collection changes', function () {
    $user = User::factory()->withProfile()->create();
    $collection = edgeCollection($user);

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('gotoPage', 3)
        ->assertSet('paginators.page', 3)
        ->set('collection', $collection->slug)
        ->assertSet('paginators.page', 1);
});

/**
 * Covers deleteCollection() branch (lines 97-98): when deleting the currently
 * active collection, the URL filter is cleared back to null.
 */
it('clears the active collection filter when that collection is deleted', function () {
    $user = User::factory()->withProfile()->create();
    $collection = edgeCollection($user, 'Active One');

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->set('collection', $collection->slug)
        ->call('confirmDeleteCollection', $collection->id)
        ->call('deleteCollection')
        ->assertSet('collection', null)
        ->assertSet('collectionIdBeingDeleted', null);

    $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
});

/**
 * Covers the unsave() move-to-general branch (lines 135-140).
 */
it('moves a post to the general list instead of fully removing it when inside a collection', function () {
    $user = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create();
    $collection = edgeCollection($user);
    $user->savedPosts()->attach($post->id, ['collection_id' => $collection->id]);

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->set('collection', $collection->slug)
        ->call('confirmUnsave', $post->id)
        ->assertSet('isRemovingFromCollection', true)
        ->call('unsave')
        ->assertSet('postIdBeingRemoved', null)
        ->assertSet('isRemovingFromCollection', false)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('saved_posts', [
        'user_id' => $user->id,
        'post_id' => $post->id,
        'collection_id' => null,
    ]);
});

/**
 * Covers getCurrentCollectionId() resolution (lines 221-224): a real slug
 * resolves to the collection id used when listing saved posts.
 */
it('resolves the current collection id from the slug when listing saved posts', function () {
    $user = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create();
    $collection = edgeCollection($user, 'Favorites');
    $user->savedPosts()->attach($post->id, ['collection_id' => $collection->id]);

    $this->actingAs($user);

    $component = Livewire::test(SavedIndex::class)
        ->set('collection', $collection->slug);

    expect($component->get('savedPosts')->pluck('id'))->toContain($post->id);
});
