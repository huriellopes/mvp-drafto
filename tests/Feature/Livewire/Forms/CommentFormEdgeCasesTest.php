<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Enums\CommentStatusEnum;
use App\Livewire\Dashboard\Comments\CommentIndex;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;

/**
 * Covers the moderation log branch of CommentForm::update() (lines 53-64),
 * which fires only when the comment status actually changes.
 */
it('logs to telegram support when a moderator changes the comment status', function () {
    Log::shouldReceive('channel')
        ->with('telegram_support')
        ->once()
        ->andReturnSelf();
    Log::shouldReceive('info')->once();

    $author = User::factory()->writer()->active()->create();
    $admin = User::factory()->superAdmin()->active()->create();
    $post = Post::factory()->published()->create(['user_id' => $author->id]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'status' => CommentStatusEnum::VISIBLE,
    ]);

    Livewire::actingAs($admin)
        ->test(CommentIndex::class)
        ->call('edit', $comment->id)
        ->set('form.status', CommentStatusEnum::HIDDEN->value)
        ->call('save')
        ->assertHasNoErrors();

    expect($comment->fresh()->status)->toBe(CommentStatusEnum::HIDDEN);
});

it('does not log when the status is unchanged', function () {
    Log::shouldReceive('channel')->never();

    $author = User::factory()->writer()->active()->create();
    $post = Post::factory()->published()->create(['user_id' => $author->id]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'status' => CommentStatusEnum::VISIBLE,
    ]);

    Livewire::actingAs($author)
        ->test(CommentIndex::class)
        ->call('edit', $comment->id)
        ->set('form.content', 'Apenas editando o texto sem mudar status.')
        ->call('save')
        ->assertHasNoErrors();
});

it('falls back to anonymous author name in the moderation log', function () {
    Log::shouldReceive('channel')
        ->with('telegram_support')
        ->once()
        ->andReturnSelf();
    Log::shouldReceive('info')->once();

    $admin = User::factory()->superAdmin()->active()->create();
    $post = Post::factory()->published()->create();

    // Guest comment (user_id null) so the author name fallback branch is hit.
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => null,
        'status' => CommentStatusEnum::VISIBLE,
    ]);

    Livewire::actingAs($admin)
        ->test(CommentIndex::class)
        ->call('edit', $comment->id)
        ->set('form.status', CommentStatusEnum::HIDDEN->value)
        ->call('save')
        ->assertHasNoErrors();
});
