<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Comments;

use App\Enums\CommentStatusEnum;
use App\Livewire\Dashboard\Comments\CommentIndex;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

it('toggles sort column and direction', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(CommentIndex::class)
        ->call('sortBy', 'created_at')
        ->assertSet('sort', 'created_at')
        ->assertSet('direction', 'asc')
        ->call('sortBy', 'created_at')
        ->assertSet('direction', 'desc')
        ->call('sortBy', 'status')
        ->assertSet('sort', 'status')
        ->assertSet('direction', 'asc');
});

it('resets pagination when search or status changes', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(CommentIndex::class)
        ->set('search', 'foo')
        ->set('status', CommentStatusEnum::VISIBLE->value)
        ->assertOk();
});

it('opens the edit modal for an owned comment', function () {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->visible()->forPost($post)->byUser($author)->create();

    Livewire::actingAs($author)
        ->test(CommentIndex::class)
        ->call('edit', $comment->id)
        ->assertSet('form.content', $comment->content)
        ->assertDispatched('open-modal', name: 'edit-comment-modal');
});

it('saves an edited comment by its author', function () {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->visible()->forPost($post)->byUser($author)->create();

    Livewire::actingAs($author)
        ->test(CommentIndex::class)
        ->call('edit', $comment->id)
        ->set('form.content', 'Conteúdo editado pelo autor')
        ->call('save')
        ->assertDispatched('close-modal', name: 'edit-comment-modal');

    expect($comment->fresh()->content)->toBe('Conteúdo editado pelo autor');
});

it('confirms and deletes an owned comment', function () {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->visible()->forPost($post)->byUser($author)->create();

    Livewire::actingAs($author)
        ->test(CommentIndex::class)
        ->call('confirmDelete', $comment->id)
        ->assertSet('commentIdBeingDeleted', $comment->id)
        ->assertDispatched('open-modal', name: 'confirm-comment-deletion')
        ->call('delete')
        ->assertSet('commentIdBeingDeleted', null);

    expect(Comment::query()->whereKey($comment->id)->exists())->toBeFalse();
});

it('returns early from delete when nothing is selected', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(CommentIndex::class)
        ->call('delete')
        ->assertOk();
});
