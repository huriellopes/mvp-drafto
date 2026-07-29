<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Saved;

use App\Actions\Saved\ListSavedPostsAction;
use App\DTOs\SavedPostsFilterData;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;

beforeEach(function () {
    $this->action = app(ListSavedPostsAction::class);
});

it('lists only the published public posts saved by the user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Post::factory()->published()->public()->create();
    $someoneElses = Post::factory()->published()->public()->create();

    $user->savedPosts()->attach($mine->id);
    $other->savedPosts()->attach($someoneElses->id);

    $result = $this->action->exec($user, new SavedPostsFilterData);

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($mine->id);
});

it('excludes unpublished posts from the saved list', function () {
    $user = User::factory()->create();
    $draft = Post::factory()->draft()->public()->create();

    $user->savedPosts()->attach($draft->id);

    $result = $this->action->exec($user, new SavedPostsFilterData);

    expect($result->total())->toBe(0);
});

it('filters saved posts by category', function () {
    $user = User::factory()->create();
    $category = PostCategory::factory()->create();

    $inCategory = Post::factory()->published()->public()->create(['category_id' => $category->id]);
    $other = Post::factory()->published()->public()->create();

    $user->savedPosts()->attach([$inCategory->id, $other->id]);

    $result = $this->action->exec($user, new SavedPostsFilterData(categoryId: $category->id));

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($inCategory->id);
});

it('filters saved posts by collection', function () {
    $user = User::factory()->create();
    $collection = $user->collections()->create(['name' => 'Box', 'slug' => 'box']);

    $inCollection = Post::factory()->published()->public()->create();
    $loose = Post::factory()->published()->public()->create();

    $user->savedPosts()->attach($inCollection->id, ['collection_id' => $collection->id]);
    $user->savedPosts()->attach($loose->id);

    $result = $this->action->exec($user, new SavedPostsFilterData(collectionId: $collection->id));

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($inCollection->id);
});

it('filters saved posts by search term', function () {
    $user = User::factory()->create();
    $match = Post::factory()->published()->public()->create(['title' => 'Distinct Saved Title']);
    $other = Post::factory()->published()->public()->create();

    $user->savedPosts()->attach([$match->id, $other->id]);

    $result = $this->action->exec($user, new SavedPostsFilterData(search: 'Distinct Saved'));

    expect($result->total())->toBe(1);
});
