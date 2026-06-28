<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Saved;

use App\Livewire\Dashboard\Saved\SavedIndex;
use App\Models\Collection;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

function makeCollection(User $user, string $name = 'Reading'): Collection
{
    return Collection::create([
        'user_id' => $user->id,
        'name' => $name,
        'slug' => Str::slug($name) . '-' . Str::lower(Str::random(6)),
    ]);
}

it('renders the saved index page', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('dashboard.posts.saved'))
        ->assertOk()
        ->assertSeeLivewire(SavedIndex::class);
});

it('exposes collections, categories and saved posts computed properties', function () {
    $user = User::factory()->withProfile()->create();
    makeCollection($user, 'Reading');

    $this->actingAs($user);

    $component = Livewire::test(SavedIndex::class);

    expect($component->get('collections'))->toHaveCount(1);
    expect($component->get('categories'))->not->toBeNull();
    expect($component->get('savedPosts'))->not->toBeNull();
});

it('resets pagination when search changes', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('gotoPage', 3)
        ->assertSet('paginators.page', 3)
        ->set('search', 'laravel')
        ->assertSet('paginators.page', 1);
});

it('creates a collection', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->set('collectionForm.name', 'My Collection')
        ->set('collectionForm.slug', 'my-collection')
        ->call('createCollection')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', name: 'new-collection-modal');

    $this->assertDatabaseHas('collections', [
        'user_id' => $user->id,
        'name' => 'My Collection',
    ]);
});

it('opens the edit collection modal and loads the collection', function () {
    $user = User::factory()->withProfile()->create();
    $collection = makeCollection($user, 'Editable');

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('openEditCollectionModal', $collection->id)
        ->assertSet('collectionForm.name', 'Editable')
        ->assertDispatched('open-modal', name: 'edit-collection-modal');
});

it('updates a collection', function () {
    $user = User::factory()->withProfile()->create();
    $collection = makeCollection($user, 'Old Name');

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('openEditCollectionModal', $collection->id)
        ->set('collectionForm.name', 'Updated Name')
        ->call('updateCollection')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', name: 'edit-collection-modal');

    expect($collection->fresh()->name)->toBe('Updated Name');
});

it('confirms and deletes a collection', function () {
    $user = User::factory()->withProfile()->create();
    $collection = makeCollection($user);

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('confirmDeleteCollection', $collection->id)
        ->assertSet('collectionIdBeingDeleted', $collection->id)
        ->assertDispatched('open-modal', name: 'confirm-delete-collection')
        ->call('deleteCollection')
        ->assertSet('collectionIdBeingDeleted', null);

    $this->assertDatabaseMissing('collections', ['id' => $collection->id]);
});

it('does nothing when deleting with no collection selected', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('deleteCollection')
        ->assertHasNoErrors();
});

it('confirms unsave outside a collection context', function () {
    $user = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create();
    $user->savedPosts()->attach($post->id);

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('confirmUnsave', $post->id)
        ->assertSet('postIdBeingRemoved', $post->id)
        ->assertSet('isRemovingFromCollection', false)
        ->assertDispatched('open-modal', name: 'confirm-unsave-post')
        ->call('unsave')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('saved_posts', [
        'user_id' => $user->id,
        'post_id' => $post->id,
    ]);
});

it('does nothing on unsave when no post is being removed', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('unsave')
        ->assertHasNoErrors();
});

it('opens the move modal with the current collection', function () {
    $user = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create();
    $collection = makeCollection($user);

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('openMoveModal', $post->id, $collection->id)
        ->assertSet('postIdBeingMoved', $post->id)
        ->assertSet('targetCollectionId', $collection->id)
        ->assertDispatched('open-modal', name: 'move-to-collection-modal');
});

it('moves a post to a target collection', function () {
    $user = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create();
    $collection = makeCollection($user);
    $user->savedPosts()->attach($post->id);

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->set('postIdBeingMoved', $post->id)
        ->set('targetCollectionId', $collection->id)
        ->call('moveToCollection')
        ->assertSet('postIdBeingMoved', null)
        ->assertSet('targetCollectionId', null)
        ->assertDispatched('close-modal', name: 'move-to-collection-modal');

    $this->assertDatabaseHas('saved_posts', [
        'user_id' => $user->id,
        'post_id' => $post->id,
        'collection_id' => $collection->id,
    ]);
});

it('does nothing when moving with no post selected', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(SavedIndex::class)
        ->call('moveToCollection')
        ->assertHasNoErrors();
});
