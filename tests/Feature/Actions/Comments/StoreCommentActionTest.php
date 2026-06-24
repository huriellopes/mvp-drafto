<?php

declare(strict_types=1);

use App\Actions\Comments\StoreCommentAction;
use App\DTOs\SaveCommentData;
use App\Enums\CommentStatusEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->action = app(StoreCommentAction::class);
});

it('stores a comment for a user on a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $data = new SaveCommentData(content: 'This is a great post!');

    $comment = $this->action->exec($user, $post, $data);

    expect($comment)->toBeInstanceOf(Comment::class)
        ->and($comment->user_id)->toBe($user->id)
        ->and($comment->post_id)->toBe($post->id)
        ->and($comment->parent_id)->toBeNull()
        ->and($comment->status)->toBe(CommentStatusEnum::VISIBLE)
        ->and($comment->content)->toContain('This is a great post!');

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'post_id' => $post->id,
        'user_id' => $user->id,
        'status' => CommentStatusEnum::VISIBLE->value,
    ]);
});

it('stores a guest comment with a null user', function () {
    $post = Post::factory()->create();

    $data = new SaveCommentData(content: 'Anonymous thoughts');

    $comment = $this->action->exec(null, $post, $data);

    expect($comment->user_id)->toBeNull();

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'user_id' => null,
        'post_id' => $post->id,
    ]);
});

it('stores a reply referencing a parent comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $parent = Comment::factory()->forPost($post)->create();

    $data = new SaveCommentData(content: 'A reply', parent_id: $parent->id);

    $comment = $this->action->exec($user, $post, $data);

    expect($comment->parent_id)->toBe($parent->id);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'parent_id' => $parent->id,
    ]);
});

it('sanitizes malicious html from the content', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $data = new SaveCommentData(content: '<script>alert("xss")</script>Hello');

    $comment = $this->action->exec($user, $post, $data);

    expect($comment->content)->not->toContain('<script>')
        ->and($comment->content)->toContain('Hello');
});
