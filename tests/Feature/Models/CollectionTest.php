<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Collection;
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('is fillable with the expected attributes', function () {
    $user = User::factory()->create();

    $collection = Collection::create([
        'user_id' => $user->id,
        'name' => 'Favorites',
        'slug' => 'favorites',
        'description' => 'My favorite posts',
    ]);

    expect($collection->name)->toBe('Favorites')
        ->and($collection->slug)->toBe('favorites')
        ->and($collection->description)->toBe('My favorite posts')
        ->and($collection->user_id)->toBe($user->id);
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $collection = Collection::create([
        'user_id' => $user->id,
        'name' => 'Favorites',
        'slug' => 'favorites',
    ]);

    expect($collection->user())->toBeInstanceOf(BelongsTo::class)
        ->and($collection->user->id)->toBe($user->id);
});

it('has many saved posts', function () {
    $user = User::factory()->create();
    $collection = Collection::create([
        'user_id' => $user->id,
        'name' => 'Favorites',
        'slug' => 'favorites',
    ]);
    $post = Post::factory()->published()->create();

    SavedPost::create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'collection_id' => $collection->id,
    ]);

    expect($collection->savedPosts())->toBeInstanceOf(HasMany::class)
        ->and($collection->savedPosts)->toHaveCount(1);
});
