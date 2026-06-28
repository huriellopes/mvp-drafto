<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Livewire\Dashboard\Admin\ShortLinkIndex;
use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Livewire\Livewire;

function makeShortLink(array $attrs = []): ShortLink
{
    $post = Post::factory()->published()->create();

    return ShortLink::factory()->create(array_merge([
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ], $attrs));
}

it('blocks non-admins from the short links page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.short-links.index'))
        ->assertForbidden();
});

it('lets an admin open the short links page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.short-links.index'))
        ->assertOk()
        ->assertSeeLivewire(ShortLinkIndex::class);
});

it('lists short links and global stats', function () {
    $admin = User::factory()->superAdmin()->create();
    makeShortLink(['code' => 'abc123', 'clicks' => 5]);
    makeShortLink(['code' => 'def456', 'clicks' => 3]);

    Livewire::actingAs($admin)
        ->test(ShortLinkIndex::class)
        ->assertSee('abc123')
        ->assertSee('def456')
        ->assertSet('globalStats.total_links', 2)
        ->assertSet('globalStats.total_clicks', 8);
});

it('toggles sort direction', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(ShortLinkIndex::class)
        ->call('sortBy', 'clicks')
        ->assertSet('sort', 'clicks')
        ->assertSet('direction', 'asc')
        ->call('sortBy', 'clicks')
        ->assertSet('direction', 'desc');
});

it('confirms and deletes a short link', function () {
    $admin = User::factory()->superAdmin()->create();
    $link = makeShortLink(['code' => 'todelete']);

    Livewire::actingAs($admin)
        ->test(ShortLinkIndex::class)
        ->call('confirmDeletion', $link->id)
        ->assertSet('linkIdBeingDeleted', $link->id)
        ->assertDispatched('open-modal', name: 'confirm-link-deletion')
        ->call('delete')
        ->assertSet('linkIdBeingDeleted', null);

    expect(ShortLink::query()->whereKey($link->id)->exists())->toBeFalse();
});
