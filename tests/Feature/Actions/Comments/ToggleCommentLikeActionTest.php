<?php

declare(strict_types=1);

use App\Actions\Comments\ToggleCommentLikeAction;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->action = new ToggleCommentLikeAction();
});

it('adds a like for an authenticated user', function () {
    Notification::fake();

    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    $liked = $this->action->exec($user, $comment);

    expect($liked)->toBeTrue();

    $this->assertDatabaseHas('comment_likes', [
        'comment_id' => $comment->id,
        'user_id' => $user->id,
        'ip_address' => null,
    ]);
});

it('removes an existing like for an authenticated user', function () {
    Notification::fake();

    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    $this->action->exec($user, $comment);
    $unliked = $this->action->exec($user, $comment);

    expect($unliked)->toBeFalse();

    $this->assertDatabaseMissing('comment_likes', [
        'comment_id' => $comment->id,
        'user_id' => $user->id,
    ]);
});

it('tracks a guest like by ip address', function () {
    $comment = Comment::factory()->create();

    $liked = $this->action->exec(null, $comment, '203.0.113.10');

    expect($liked)->toBeTrue();

    $this->assertDatabaseHas('comment_likes', [
        'comment_id' => $comment->id,
        'user_id' => null,
        'ip_address' => '203.0.113.10',
    ]);
});

it('notifies the comment author when another user likes the comment', function () {
    Notification::fake();

    $author = User::factory()->create();
    $liker = User::factory()->create();
    $comment = Comment::factory()->byUser($author)->create();

    $this->action->exec($liker, $comment);

    Notification::assertSentTo(
        [$author],
        SocialInteractionNotification::class,
        function ($notification) use ($liker, $comment) {
            return $notification->type === 'like_comment'
                && $notification->causer->id === $liker->id
                && $notification->model->id === $comment->id;
        },
    );
});

it('does not notify when the author likes their own comment', function () {
    Notification::fake();

    $author = User::factory()->create();
    $comment = Comment::factory()->byUser($author)->create();

    $this->action->exec($author, $comment);

    Notification::assertNothingSent();
});
