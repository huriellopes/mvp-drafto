<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Livewire\Dashboard\Admin\PostViews\PostViewIndex;
use App\Models\PostView;
use App\Models\User;
use Livewire\Livewire;

it('blocks non-admins from the post views page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.posts.views'))
        ->assertForbidden();
});

it('lets an admin open the post views page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.posts.views'))
        ->assertOk()
        ->assertSeeLivewire(PostViewIndex::class);
});

it('renders for an admin via Livewire', function () {
    $admin = User::factory()->superAdmin()->create();
    PostView::factory()->count(2)->create();

    Livewire::actingAs($admin)
        ->test(PostViewIndex::class)
        ->assertOk();
});

it('toggles sort direction', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(PostViewIndex::class)
        ->call('sortBy', 'viewed_at')
        ->assertSet('sort', 'viewed_at')
        ->assertSet('direction', 'asc');
});

it('confirms and deletes a post view record', function () {
    $admin = User::factory()->superAdmin()->create();
    $view = PostView::factory()->create();

    Livewire::actingAs($admin)
        ->test(PostViewIndex::class)
        ->call('confirmDelete', $view->id)
        ->assertSet('viewIdToDelete', $view->id)
        ->assertDispatched('open-modal', name: 'confirm-delete-view')
        ->call('delete')
        ->assertSet('viewIdToDelete', null);

    expect(PostView::query()->whereKey($view->id)->exists())->toBeFalse();
});
