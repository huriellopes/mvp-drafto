<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Modules;

use App\Actions\Modules\GenerateShortLinkAction;
use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;

beforeEach(function () {
    $this->action = app(GenerateShortLinkAction::class);
});

function enableShortener(User $user, array $settings = []): void
{
    $module = Module::where('slug', ModuleEnum::LINK_SHORTENER->value)->firstOrFail();

    $user->modules()->syncWithoutDetaching([
        $module->id => [
            'is_enabled' => true,
            'settings' => json_encode($settings),
        ],
    ]);
}

it('returns the original profile url when the owner lacks the module', function () {
    $user = User::factory()->writer()->withProfile()->create();
    $user->modules()->detach();

    $url = $this->action->exec($user->fresh(), $user->fresh());

    expect($url)->toBe(route('profile.show', $user->profile->username));

    $this->assertDatabaseCount('short_links', 0);
});

it('creates and returns a short link for a user profile when enabled', function () {
    $user = User::factory()->writer()->withProfile()->create();
    enableShortener($user);

    $url = $this->action->exec($user, $user->fresh());

    $shortLink = ShortLink::first();

    expect($shortLink)->not->toBeNull()
        ->and($shortLink->user_id)->toBe($user->id)
        ->and($url)->toBe(route('shortlink.redirect', $shortLink->code));
});

it('creates a short link for a post owned by an enabled author', function () {
    $author = User::factory()->writer()->withProfile()->create();
    enableShortener($author);
    $post = Post::factory()->published()->create(['user_id' => $author->id]);

    $url = $this->action->exec($author->fresh(), $post);

    $shortLink = ShortLink::first();

    expect($shortLink->shortable_id)->toBe($post->id)
        ->and($url)->toBe(route('shortlink.redirect', $shortLink->code));
});

it('reuses an existing short link instead of creating a new one', function () {
    $user = User::factory()->writer()->withProfile()->create();
    enableShortener($user);

    $first = $this->action->exec($user->fresh(), $user->fresh());
    $second = $this->action->exec($user->fresh(), $user->fresh());

    expect($first)->toBe($second);
    $this->assertDatabaseCount('short_links', 1);
});

it('falls back to the original url when profile shortening is disabled', function () {
    $user = User::factory()->writer()->withProfile()->create();
    enableShortener($user, ['enable_for_profile' => false]);

    $url = $this->action->exec($user->fresh(), $user->fresh());

    expect($url)->toBe(route('profile.show', $user->profile->username));
    $this->assertDatabaseCount('short_links', 0);
});
