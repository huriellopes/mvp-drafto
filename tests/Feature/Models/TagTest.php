<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

it('is fillable with the expected attributes', function () {
    $user = User::factory()->create();

    $tag = Tag::create([
        'user_id' => $user->id,
        'name' => 'php',
        'slug' => 'php',
    ]);

    expect($tag->name)->toBe('php')
        ->and($tag->slug)->toBe('php')
        ->and($tag->user_id)->toBe($user->id);
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create(['user_id' => $user->id]);

    expect($tag->user())->toBeInstanceOf(BelongsTo::class)
        ->and($tag->user->id)->toBe($user->id);
});

it('belongs to many posts through the post_tag pivot', function () {
    $tag = Tag::factory()->create();
    $post = Post::factory()->published()->create();

    $tag->posts()->attach($post->id);

    expect($tag->posts())->toBeInstanceOf(BelongsToMany::class)
        ->and($tag->posts)->toHaveCount(1)
        ->and($tag->posts->first()->id)->toBe($post->id);
});
