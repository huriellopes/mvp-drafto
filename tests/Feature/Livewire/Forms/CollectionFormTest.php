<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Dashboard\Saved\SavedIndex;
use App\Models\Collection;
use App\Models\User;
use Livewire\Livewire;

it('creates a collection through the saved index', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(SavedIndex::class)
        ->set('collectionForm.name', 'Favoritos')
        ->set('collectionForm.slug', 'favoritos')
        ->set('collectionForm.description', 'Meus posts favoritos')
        ->call('createCollection')
        ->assertHasNoErrors();

    expect(Collection::query()
        ->where('user_id', $user->id)
        ->where('slug', 'favoritos')
        ->exists())->toBeTrue();
});

it('requires name and slug when creating a collection', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(SavedIndex::class)
        ->set('collectionForm.name', '')
        ->set('collectionForm.slug', '')
        ->call('createCollection')
        ->assertHasErrors([
            'collectionForm.name' => 'required',
            'collectionForm.slug' => 'required',
        ]);
});

it('rejects a duplicate collection slug for the same user', function () {
    $user = User::factory()->writer()->create();
    Collection::query()->create([
        'user_id' => $user->id,
        'name' => 'Existente',
        'slug' => 'existente',
    ]);

    Livewire::actingAs($user)
        ->test(SavedIndex::class)
        ->set('collectionForm.name', 'Outro Nome')
        ->set('collectionForm.slug', 'existente')
        ->call('createCollection')
        ->assertHasErrors(['collectionForm.slug']);
});

it('updates an existing collection', function () {
    $user = User::factory()->writer()->create();
    $collection = Collection::query()->create([
        'user_id' => $user->id,
        'name' => 'Antiga',
        'slug' => 'antiga',
        'description' => 'desc',
    ]);

    Livewire::actingAs($user)
        ->test(SavedIndex::class)
        ->call('openEditCollectionModal', $collection->id)
        ->assertSet('collectionForm.name', 'Antiga')
        ->set('collectionForm.name', 'Renomeada')
        ->set('collectionForm.slug', 'renomeada')
        ->call('updateCollection')
        ->assertHasNoErrors();

    expect($collection->fresh()->name)->toBe('Renomeada')
        ->and($collection->fresh()->slug)->toBe('renomeada');
});
