<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;

it('belongs to a user and has many posts', function () {
    $user = User::factory()->create();
    $category = PostCategory::factory()->create(['user_id' => $user->id]);
    Post::factory()->create(['category_id' => $category->id]);

    expect($category->user->id)->toBe($user->id)
        ->and($category->posts)->toHaveCount(1);
});

it('scopes global categories when no user id is provided', function () {
    PostCategory::factory()->create(['user_id' => null]);
    PostCategory::factory()->create(['user_id' => User::factory()->create()->id]);

    $results = PostCategory::forUser()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->user_id)->toBeNull();
});

it('scopes global plus owned categories for a given user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    PostCategory::factory()->create(['user_id' => null]);
    PostCategory::factory()->create(['user_id' => $user->id]);
    PostCategory::factory()->create(['user_id' => $other->id]);

    $results = PostCategory::forUser($user->id)->get();

    expect($results)->toHaveCount(2);
});
