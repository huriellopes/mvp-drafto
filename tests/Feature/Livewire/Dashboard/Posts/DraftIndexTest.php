<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Posts;

use App\Livewire\Dashboard\Posts\DraftIndex;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

it('lists only the drafts of the authenticated user', function () {
    $user = User::factory()->writer()->create();
    $draft = Post::factory()->draft()->for($user)->create(['title' => 'Meu Rascunho']);
    Post::factory()->published()->for($user)->create(['title' => 'Publicado']);

    Livewire::actingAs($user)
        ->test(DraftIndex::class)
        ->call('$refresh') // Componente é #[Lazy]; força o render real.
        ->assertSee('Meu Rascunho')
        ->assertDontSee('Publicado');
});

it('toggles sort field and direction', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(DraftIndex::class)
        ->call('sortBy', 'title')
        ->assertSet('sort', 'title')
        ->assertSet('direction', 'asc')
        ->call('sortBy', 'title')
        ->assertSet('direction', 'desc')
        ->call('sortBy', 'created_at')
        ->assertSet('sort', 'created_at')
        ->assertSet('direction', 'asc');
});

it('resets pagination when searching', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(DraftIndex::class)
        ->set('search', 'algo')
        ->assertOk();
});

it('confirms and deletes a draft', function () {
    $user = User::factory()->writer()->create();
    $draft = Post::factory()->draft()->for($user)->create();

    Livewire::actingAs($user)
        ->test(DraftIndex::class)
        ->call('confirmDelete', $draft->id)
        ->assertSet('postIdBeingDeleted', $draft->id)
        ->assertDispatched('open-modal', name: 'confirm-post-deletion')
        ->call('deletePost')
        ->assertSet('postIdBeingDeleted', null);

    expect(Post::query()->whereKey($draft->id)->exists())->toBeFalse();
});

it('returns early from deletePost when nothing is selected', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(DraftIndex::class)
        ->call('deletePost')
        ->assertOk();
});

it('renders the full component after the lazy load', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(DraftIndex::class)
        ->call('$refresh')
        ->assertOk();
});
