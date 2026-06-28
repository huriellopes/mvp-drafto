<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Modules;

use App\Actions\Modules\GenerateShortLinkAction;
use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->action = app(GenerateShortLinkAction::class);
});

function enableShortenerEdge(User $user, array $settings = []): void
{
    $module = Module::where('slug', ModuleEnum::LINK_SHORTENER->value)->firstOrFail();

    $user->modules()->syncWithoutDetaching([
        $module->id => [
            'is_enabled' => true,
            'settings' => json_encode($settings),
        ],
    ]);
}

it('falls back to the original post url when post shortening is disabled', function () {
    $author = User::factory()->writer()->withProfile()->create();
    enableShortenerEdge($author, ['enable_for_posts' => false]);

    $post = Post::factory()->published()->create(['user_id' => $author->id]);

    $url = $this->action->exec($author->fresh(), $post);

    expect($url)->toBe(route('posts.show', $post->slug));
    $this->assertDatabaseCount('short_links', 0);
});

it('returns the home url for an unsupported shortable model when module unavailable', function () {
    $user = User::factory()->writer()->withProfile()->create();
    $user->modules()->detach();

    // Module model is neither User nor Post; with the module unavailable the action
    // short-circuits into getOriginalUrl which returns url('/') for unknown types.
    $module = Module::query()->first();

    $url = $this->action->exec($user->fresh(), $module);

    expect($url)->toBe(url('/'));
});
