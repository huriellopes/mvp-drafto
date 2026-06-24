<?php

declare(strict_types=1);

use App\Actions\PostCollections\SyncPostCollectionsAction;
use App\Enums\PostCollectionVisibilityEnum;
use App\Livewire\Dashboard\Posts\PostCollectionsIndex;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

it('lets a writer create a collection', function () {
    $writer = User::factory()->writer()->create();

    Livewire::actingAs($writer)
        ->test(PostCollectionsIndex::class)
        ->set('form.name', 'Contos de Inverno')
        ->set('form.description', 'Minha série sazonal')
        ->set('form.visibility', PostCollectionVisibilityEnum::PRIVATE->value)
        ->call('createCollection')
        ->assertHasNoErrors();

    expect(PostCollection::where('user_id', $writer->id)->where('name', 'Contos de Inverno')->exists())->toBeTrue();
});

it('attaches and detaches one of the writer own posts via the organize modal', function () {
    $writer = User::factory()->writer()->create();
    $collection = PostCollection::factory()->for($writer)->create();
    $post = Post::factory()->for($writer)->create();

    $component = Livewire::actingAs($writer)
        ->test(PostCollectionsIndex::class)
        ->call('openCollections', $post->id)
        ->call('toggleCollectionForPost', $collection->id);

    expect($collection->posts()->whereKey($post->id)->exists())->toBeTrue();

    $component->call('toggleCollectionForPost', $collection->id);

    expect($collection->posts()->whereKey($post->id)->exists())->toBeFalse();
});

it('removes a post from the active collection', function () {
    $writer = User::factory()->writer()->create();
    $collection = PostCollection::factory()->for($writer)->create();
    $post = Post::factory()->for($writer)->create();
    $collection->posts()->attach($post->id);

    Livewire::actingAs($writer)
        ->test(PostCollectionsIndex::class)
        ->set('collection', $collection->slug)
        ->call('removeFromActiveCollection', $post->id)
        ->assertHasNoErrors();

    expect($collection->posts()->whereKey($post->id)->exists())->toBeFalse();
});

it('a post can belong to multiple collections (N:N)', function () {
    $writer = User::factory()->writer()->create();
    $a = PostCollection::factory()->for($writer)->create();
    $b = PostCollection::factory()->for($writer)->create();
    $post = Post::factory()->for($writer)->create();

    $post->collections()->attach([$a->id, $b->id]);

    expect($post->collections()->count())->toBe(2);
});

it('syncs only the collections owned by the post author', function () {
    $writer = User::factory()->writer()->create();
    $other = User::factory()->writer()->create();

    $own = PostCollection::factory()->for($writer)->create();
    $foreign = PostCollection::factory()->for($other)->create();
    $post = Post::factory()->for($writer)->create();

    app(SyncPostCollectionsAction::class)->exec($post, [$own->id, $foreign->id]);

    $ids = $post->collections()->pluck('post_collections.id');

    expect($ids)->toContain($own->id)
        ->and($ids)->not->toContain($foreign->id);
});

it('prevents a writer from managing another writer collection', function () {
    $owner = User::factory()->writer()->create();
    $intruder = User::factory()->writer()->create();
    $collection = PostCollection::factory()->for($owner)->create();

    Livewire::actingAs($intruder)
        ->test(PostCollectionsIndex::class)
        ->call('openEditModal', $collection->id);
})->throws(ModelNotFoundException::class);

it('deletes the collection but keeps the posts', function () {
    $writer = User::factory()->writer()->create();
    $collection = PostCollection::factory()->for($writer)->create();
    $post = Post::factory()->for($writer)->create();
    $collection->posts()->attach($post->id);

    Livewire::actingAs($writer)
        ->test(PostCollectionsIndex::class)
        ->call('confirmDelete', $collection->id)
        ->call('deleteCollection')
        ->assertHasNoErrors();

    expect(PostCollection::find($collection->id))->toBeNull()
        ->and(Post::find($post->id))->not->toBeNull();
});
