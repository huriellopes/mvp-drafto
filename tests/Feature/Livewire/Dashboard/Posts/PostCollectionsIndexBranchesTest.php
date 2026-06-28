<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Posts;

use App\Livewire\Dashboard\Posts\PostCollectionsIndex;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\User;
use Livewire\Livewire;

it('resets pagination when search, collection or status filter change', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(PostCollectionsIndex::class)
        ->set('search', 'foo')
        ->set('statusFilter', 'draft')
        ->assertOk();
});

it('opens the edit modal and updates an owned collection', function () {
    $user = User::factory()->writer()->create();
    $collection = PostCollection::factory()->for($user)->create(['name' => 'Antigo']);

    Livewire::actingAs($user)
        ->test(PostCollectionsIndex::class)
        ->call('openEditModal', $collection->id)
        ->assertDispatched('open-modal', name: 'edit-post-collection-modal')
        ->set('form.name', 'Nome Atualizado')
        ->call('updateCollection')
        ->assertDispatched('close-modal', name: 'edit-post-collection-modal');

    expect($collection->fresh()->name)->toBe('Nome Atualizado');
});

it('selects an active collection and filters its posts', function () {
    $user = User::factory()->writer()->create();
    $collection = PostCollection::factory()->for($user)->create();
    $inCollection = Post::factory()->published()->for($user)->create(['title' => 'Dentro da Coleção']);
    $outside = Post::factory()->published()->for($user)->create(['title' => 'Fora da Coleção']);
    $collection->posts()->attach($inCollection->id);

    Livewire::actingAs($user)
        ->test(PostCollectionsIndex::class)
        ->set('collection', $collection->slug)
        ->assertSet('activeCollection.id', $collection->id)
        ->assertSee('Dentro da Coleção')
        ->assertDontSee('Fora da Coleção');
});

it('filters owned posts by draft and published status', function () {
    $user = User::factory()->writer()->create();
    Post::factory()->published()->for($user)->create(['title' => 'Pub Title']);
    Post::factory()->draft()->for($user)->create(['title' => 'Draft Title']);

    Livewire::actingAs($user)
        ->test(PostCollectionsIndex::class)
        ->set('statusFilter', 'published')
        ->assertSee('Pub Title')
        ->assertDontSee('Draft Title')
        ->set('statusFilter', 'draft')
        ->assertSee('Draft Title')
        ->assertDontSee('Pub Title');
});

it('returns early from deleteCollection when nothing is selected', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(PostCollectionsIndex::class)
        ->call('deleteCollection')
        ->assertOk();
});

it('clears the active collection when the active one is deleted', function () {
    $user = User::factory()->writer()->create();
    $collection = PostCollection::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(PostCollectionsIndex::class)
        ->set('collection', $collection->slug)
        ->call('confirmDelete', $collection->id)
        ->call('deleteCollection')
        ->assertSet('collection', null);

    expect(PostCollection::find($collection->id))->toBeNull();
});

it('returns early from removeFromActiveCollection without an active collection', function () {
    $user = User::factory()->writer()->create();
    $post = Post::factory()->published()->for($user)->create();

    Livewire::actingAs($user)
        ->test(PostCollectionsIndex::class)
        ->call('removeFromActiveCollection', $post->id)
        ->assertOk();
});
