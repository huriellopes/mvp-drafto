<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Modules;

use App\Enums\ModuleEnum;
use App\Livewire\Dashboard\Modules\LinkShortenerDashboard;
use App\Models\Module;
use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Livewire\Livewire;

function userWithShortenerModule(): User
{
    $user = User::factory()->create();
    $module = Module::query()->where('slug', ModuleEnum::LINK_SHORTENER->value)->first();
    $user->modules()->syncWithoutDetaching([
        $module->id => ['is_enabled' => true, 'settings' => json_encode([
            'enable_for_profile' => true,
            'enable_for_posts' => true,
        ])],
    ]);

    return $user;
}

function shortLinkFor(User $user, array $attrs = []): ShortLink
{
    $post = Post::factory()->published()->for($user)->create();

    return ShortLink::factory()->create(array_merge([
        'user_id' => $user->id,
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ], $attrs));
}

it('mounts with the module settings of the user', function () {
    $user = userWithShortenerModule();

    Livewire::actingAs($user)
        ->test(LinkShortenerDashboard::class)
        ->assertSet('enableForProfile', true)
        ->assertSet('enableForPosts', true);
});

it('lists only the links of the authenticated user and their stats', function () {
    $user = userWithShortenerModule();
    $other = User::factory()->create();

    shortLinkFor($user, ['code' => 'mine01', 'clicks' => 4]);
    shortLinkFor($user, ['code' => 'mine02', 'clicks' => 6]);
    shortLinkFor($other, ['code' => 'other1', 'clicks' => 99]);

    Livewire::actingAs($user)
        ->test(LinkShortenerDashboard::class)
        ->assertSee('mine01')
        ->assertSee('mine02')
        ->assertDontSee('other1')
        ->assertSet('stats.total_links', 2)
        ->assertSet('stats.total_clicks', 10);
});

it('filters links by search code', function () {
    $user = userWithShortenerModule();
    shortLinkFor($user, ['code' => 'zzfindme']);
    shortLinkFor($user, ['code' => 'zzgone99']);

    Livewire::actingAs($user)
        ->test(LinkShortenerDashboard::class)
        ->set('search', 'zzfindme')
        ->assertSee('zzfindme')
        ->assertDontSee('zzgone99');
});

it('persists the enable_for_profile setting when toggled', function () {
    $user = userWithShortenerModule();

    Livewire::actingAs($user)
        ->test(LinkShortenerDashboard::class)
        ->set('enableForProfile', false);

    expect((bool) $user->fresh()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_profile', true))->toBeFalse();
});

it('persists the enable_for_posts setting when toggled', function () {
    $user = userWithShortenerModule();

    Livewire::actingAs($user)
        ->test(LinkShortenerDashboard::class)
        ->set('enableForPosts', false);

    expect((bool) $user->fresh()->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_posts', true))->toBeFalse();
});
