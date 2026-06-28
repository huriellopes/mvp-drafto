<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Enums\CommentStatusEnum;
use App\Models\Comment;
use App\Models\Post;

it('increments the post comments count when a visible comment is created', function () {
    $post = Post::factory()->published()->create(['comments_count' => 0]);

    Comment::factory()->forPost($post)->visible()->create();

    expect($post->fresh()->comments_count)->toBe(1);
});

it('does not increment the post comments count when a hidden comment is created', function () {
    $post = Post::factory()->published()->create(['comments_count' => 0]);

    Comment::factory()->forPost($post)->hidden()->create();

    expect($post->fresh()->comments_count)->toBe(0);
});

it('increments the count when a hidden comment becomes visible', function () {
    $post = Post::factory()->published()->create(['comments_count' => 0]);
    $comment = Comment::factory()->forPost($post)->hidden()->create();

    $comment->update(['status' => CommentStatusEnum::VISIBLE]);

    expect($post->fresh()->comments_count)->toBe(1);
});

it('decrements the count when a visible comment is hidden', function () {
    $post = Post::factory()->published()->create(['comments_count' => 0]);
    $comment = Comment::factory()->forPost($post)->visible()->create();

    expect($post->fresh()->comments_count)->toBe(1);

    $comment->update(['status' => CommentStatusEnum::HIDDEN]);

    expect($post->fresh()->comments_count)->toBe(0);
});

it('does not change the count when an unrelated field changes on a comment', function () {
    $post = Post::factory()->published()->create(['comments_count' => 0]);
    $comment = Comment::factory()->forPost($post)->visible()->create();

    $comment->update(['content' => 'edited content']);

    expect($post->fresh()->comments_count)->toBe(1);
});

it('decrements the count when a visible comment is deleted', function () {
    $post = Post::factory()->published()->create(['comments_count' => 0]);
    $comment = Comment::factory()->forPost($post)->visible()->create();

    expect($post->fresh()->comments_count)->toBe(1);

    $comment->delete();

    expect($post->fresh()->comments_count)->toBe(0);
});

it('does not decrement the count when a hidden comment is deleted', function () {
    $post = Post::factory()->published()->create(['comments_count' => 5]);
    $comment = Comment::factory()->forPost($post)->hidden()->create();

    $comment->delete();

    expect($post->fresh()->comments_count)->toBe(5);
});
