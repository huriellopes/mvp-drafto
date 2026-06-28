<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Collection;
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

it('is an incrementing pivot using the saved_posts table', function () {
    $savedPost = new SavedPost();

    expect($savedPost)->toBeInstanceOf(Pivot::class)
        ->and($savedPost->incrementing)->toBeTrue()
        ->and($savedPost->getTable())->toBe('saved_posts');
});

it('is fillable and persists the relationships', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $collection = Collection::create([
        'user_id' => $user->id,
        'name' => 'Favorites',
        'slug' => 'favorites',
    ]);

    $savedPost = SavedPost::create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'collection_id' => $collection->id,
    ]);

    expect($savedPost->user())->toBeInstanceOf(BelongsTo::class)
        ->and($savedPost->user->id)->toBe($user->id)
        ->and($savedPost->post())->toBeInstanceOf(BelongsTo::class)
        ->and($savedPost->post->id)->toBe($post->id)
        ->and($savedPost->collection())->toBeInstanceOf(BelongsTo::class)
        ->and($savedPost->collection->id)->toBe($collection->id);
});
