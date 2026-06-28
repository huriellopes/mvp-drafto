<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

it('is fillable with the expected attributes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $link = ShortLink::create([
        'user_id' => $user->id,
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
        'code' => 'abc123',
        'clicks' => 5,
    ]);

    expect($link->code)->toBe('abc123')
        ->and($link->clicks)->toBe(5)
        ->and($link->user_id)->toBe($user->id);
});

it('belongs to the owning user', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $link = ShortLink::factory()->create([
        'user_id' => $user->id,
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ]);

    expect($link->user())->toBeInstanceOf(BelongsTo::class)
        ->and($link->user->id)->toBe($user->id);
});

it('resolves the morph-to shortable target', function () {
    $post = Post::factory()->published()->create();
    $link = ShortLink::factory()->create([
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ]);

    expect($link->shortable())->toBeInstanceOf(MorphTo::class)
        ->and($link->shortable)->toBeInstanceOf(Post::class)
        ->and($link->shortable->id)->toBe($post->id);
});
