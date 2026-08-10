<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Saved;

use App\Actions\Saved\UpdateCollectionAction;
use App\DTOs\CollectionData;
use App\Models\User;

beforeEach(function () {
    $this->action = app(UpdateCollectionAction::class);
});

it('updates the collection attributes', function () {
    $user = User::factory()->create();
    $collection = $user->collections()->create(['name' => 'Old', 'slug' => 'old']);

    // Segurança (IDOR): a Action agora reconfirma a posse via CollectionPolicy
    // internamente, então precisa de um usuário autenticado no contexto.
    $this->actingAs($user);

    $updated = $this->action->exec($collection, new CollectionData(
        name: 'New Name',
        description: 'New description',
    ));

    expect($updated->id)->toBe($collection->id)
        ->and($updated->name)->toBe('New Name')
        ->and($updated->slug)->toBe('new-name')
        ->and($updated->description)->toBe('New description');
});

it('keeps the provided slug', function () {
    $user = User::factory()->create();
    $collection = $user->collections()->create(['name' => 'Old', 'slug' => 'old']);

    $this->actingAs($user);

    $updated = $this->action->exec($collection, new CollectionData(
        name: 'New Name',
        slug: 'kept-slug',
    ));

    expect($updated->slug)->toBe('kept-slug');
});

it('does not update a collection belonging to another user', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $collection = $owner->collections()->create(['name' => 'Old', 'slug' => 'old']);

    $this->actingAs($intruder);

    $this->action->exec($collection, new CollectionData(name: 'Hacked'));

    expect($collection->fresh()->name)->toBe('Old');
});
