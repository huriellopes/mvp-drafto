<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Modules;

use App\Actions\Modules\ResolveShortLinkAction;
use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->action = app(ResolveShortLinkAction::class);
});

it('resolves a short link pointing at a post', function () {
    $post = Post::factory()->published()->create();
    $shortLink = ShortLink::factory()->create([
        'code' => 'POST01',
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ]);

    expect($this->action->exec($shortLink->code))->toBe(route('posts.show', $post->slug));
});

it('resolves a short link pointing at a user profile', function () {
    $user = User::factory()->withProfile()->create();
    $shortLink = ShortLink::factory()->create([
        'user_id' => $user->id,
        'code' => 'USER01',
        'shortable_type' => $user->getMorphClass(),
        'shortable_id' => $user->id,
    ]);

    expect($this->action->exec($shortLink->code))->toBe(route('profile.show', $user->profile->username));
});

it('returns null when the code does not exist', function () {
    expect($this->action->exec('missing'))->toBeNull();
});

it('caches the resolved destination', function () {
    $post = Post::factory()->published()->create();
    $shortLink = ShortLink::factory()->create([
        'code' => 'CACHED',
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ]);

    $this->action->exec($shortLink->code);

    expect(Cache::has('shortlink:CACHED'))->toBeTrue();
});
