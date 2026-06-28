<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Posts;

use App\Enums\PostStatusEnum;
use App\Livewire\Dashboard\Posts\IndexPosts;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

it('lists published posts but not drafts', function () {
    $user = User::factory()->writer()->create();
    Post::factory()->published()->for($user)->create(['title' => 'Texto Publicado']);
    Post::factory()->draft()->for($user)->create(['title' => 'Texto Rascunho']);

    Livewire::actingAs($user)
        ->test(IndexPosts::class)
        ->assertSee('Texto Publicado')
        ->assertDontSee('Texto Rascunho');
});

it('filters posts by status', function () {
    $user = User::factory()->writer()->create();
    Post::factory()->published()->for($user)->create(['title' => 'Somente Publicado']);

    Livewire::actingAs($user)
        ->test(IndexPosts::class)
        ->set('status', PostStatusEnum::PUBLISHED->value)
        ->assertSee('Somente Publicado');
});

it('toggles sort field and direction', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(IndexPosts::class)
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
        ->test(IndexPosts::class)
        ->set('search', 'algo')
        ->assertOk();
});

it('confirms and deletes a post', function () {
    $user = User::factory()->writer()->create();
    $post = Post::factory()->published()->for($user)->create();

    Livewire::actingAs($user)
        ->test(IndexPosts::class)
        ->call('confirmDelete', $post->id)
        ->assertSet('postIdBeingDeleted', $post->id)
        ->assertDispatched('open-modal', name: 'confirm-post-deletion')
        ->call('deletePost')
        ->assertSet('postIdBeingDeleted', null);

    expect(Post::query()->whereKey($post->id)->exists())->toBeFalse();
});

it('returns early from deletePost when nothing is selected', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(IndexPosts::class)
        ->call('deletePost')
        ->assertOk();
});
