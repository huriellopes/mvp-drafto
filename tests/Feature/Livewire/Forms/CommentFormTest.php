<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Enums\CommentStatusEnum;
use App\Livewire\Dashboard\Comments\CommentIndex;
use App\Livewire\Forms\Dashboard\CommentForm;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

it('lets the author edit their own comment content through the dashboard', function () {
    $user = User::factory()->writer()->active()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'status' => CommentStatusEnum::VISIBLE,
    ]);

    Livewire::actingAs($user)
        ->test(CommentIndex::class)
        ->call('edit', $comment->id)
        ->assertSet('form.content', $comment->content)
        ->set('form.content', 'Conteúdo atualizado pelo autor.')
        ->call('save')
        ->assertHasNoErrors();

    expect($comment->fresh()->content)->toBe('Conteúdo atualizado pelo autor.');
});

it('validates content length when the author edits a comment', function () {
    $user = User::factory()->writer()->active()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'status' => CommentStatusEnum::VISIBLE,
    ]);

    Livewire::actingAs($user)
        ->test(CommentIndex::class)
        ->call('edit', $comment->id)
        ->set('form.content', 'ab')
        ->call('save')
        ->assertHasErrors(['form.content']);
});

it('requires a valid status enum value', function () {
    $user = User::factory()->writer()->active()->create();
    $post = Post::factory()->published()->create();
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id,
        'status' => CommentStatusEnum::VISIBLE,
    ]);

    Livewire::actingAs($user)
        ->test(CommentIndex::class)
        ->call('edit', $comment->id)
        ->set('form.status', 'not-a-real-status')
        ->call('save')
        ->assertHasErrors(['form.status']);
});

it('treats content as nullable for a non-author moderator', function () {
    $author = User::factory()->writer()->active()->create();
    $admin = User::factory()->superAdmin()->active()->create();
    $post = Post::factory()->published()->create(['user_id' => $author->id]);
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'status' => CommentStatusEnum::VISIBLE,
    ]);

    $this->actingAs($admin);

    $form = new CommentForm(new CommentIndex(), 'form');
    $form->setComment($comment);
    $form->content = '';

    expect($form->rules()['content'])->toContain('nullable');
});
