<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\PostCollections;

use App\Actions\PostCollections\SavePostCollectionAction;
use App\DTOs\PostCollectionData;
use App\Enums\PostCollectionVisibilityEnum;
use App\Models\PostCollection;
use App\Models\User;

beforeEach(function () {
    $this->action = app(SavePostCollectionAction::class);
});

it('creates a collection for the user', function () {
    $user = User::factory()->writer()->create();

    $collection = $this->action->exec($user, new PostCollectionData(
        name: 'My Series',
        visibility: PostCollectionVisibilityEnum::PUBLIC,
    ));

    expect($collection)->toBeInstanceOf(PostCollection::class)
        ->and($collection->user_id)->toBe($user->id)
        ->and($collection->name)->toBe('My Series')
        ->and($collection->slug)->toBe('my-series')
        ->and($collection->visibility)->toBe(PostCollectionVisibilityEnum::PUBLIC);
});

it('updates an existing collection', function () {
    $user = User::factory()->writer()->create();
    $existing = PostCollection::factory()->create(['user_id' => $user->id]);

    $updated = $this->action->exec($user, new PostCollectionData(
        name: 'Renamed',
        slug: 'renamed',
        description: 'New description',
    ), $existing);

    expect($updated->id)->toBe($existing->id)
        ->and($updated->name)->toBe('Renamed')
        ->and($updated->description)->toBe('New description');
});
