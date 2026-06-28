<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('redirects a short link to the destination post and increments clicks', function () {
    $post = Post::factory()->published()->create();
    $shortLink = ShortLink::factory()->create([
        'code' => 'GO1234',
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
        'clicks' => 0,
    ]);

    $this->get(route('shortlink.redirect', ['code' => 'GO1234']))
        ->assertRedirect(route('posts.show', $post->slug));

    expect($shortLink->fresh()->clicks)->toBe(1);
});

it('redirects a short link pointing at a user profile', function () {
    $user = User::factory()->withProfile()->create();
    $shortLink = ShortLink::factory()->create([
        'user_id' => $user->id,
        'code' => 'USR777',
        'shortable_type' => $user->getMorphClass(),
        'shortable_id' => $user->id,
    ]);

    $this->get(route('shortlink.redirect', ['code' => 'USR777']))
        ->assertRedirect(route('profile.show', $user->profile->username));
});

it('returns 404 for an unknown code', function () {
    $this->get(route('shortlink.redirect', ['code' => 'nope']))
        ->assertStatus(404);
});
