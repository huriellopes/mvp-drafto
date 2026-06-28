<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Saved;

use App\Actions\Saved\CreateCollectionAction;
use App\DTOs\CollectionData;
use App\Models\Collection;
use App\Models\User;

beforeEach(function () {
    $this->action = app(CreateCollectionAction::class);
});

it('creates a collection owned by the user', function () {
    $user = User::factory()->create();

    $collection = $this->action->exec($user, new CollectionData(
        name: 'Reading List',
        description: 'Things to read',
    ));

    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collection->user_id)->toBe($user->id)
        ->and($collection->name)->toBe('Reading List')
        ->and($collection->slug)->toBe('reading-list')
        ->and($collection->description)->toBe('Things to read');
});

it('uses the provided slug when given', function () {
    $user = User::factory()->create();

    $collection = $this->action->exec($user, new CollectionData(
        name: 'Reading List',
        slug: 'custom-slug',
    ));

    expect($collection->slug)->toBe('custom-slug');
});
