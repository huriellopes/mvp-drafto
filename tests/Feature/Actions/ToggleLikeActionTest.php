<?php

declare(strict_types=1);

use App\Actions\Posts\ToggleLikeAction;
use App\Models\Post;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->action = new ToggleLikeAction();
});

it('attaches a like and increments likes_count', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['likes_count' => 0]);

    Notification::fake();

    $isAttached = $this->action->exec($user, $post);

    expect($isAttached)->toBeTrue()
        ->and($post->fresh()->likes_count)->toBe(1)
        ->and($user->likedPosts()->where('post_id', $post->id)->exists())->toBeTrue();
});

it('detaches a like and decrements likes_count', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['likes_count' => 1]);
    $user->likedPosts()->attach($post->id); // Pre-attach

    $isAttached = $this->action->exec($user, $post);

    expect($isAttached)->toBeFalse()
        ->and($post->fresh()->likes_count)->toBe(0)
        ->and($user->likedPosts()->where('post_id', $post->id)->exists())->toBeFalse();
});

it('sends notification to author if another user likes their post', function () {
    $author = User::factory()->create();
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);

    Notification::fake();

    $this->action->exec($user, $post);

    Notification::assertSentTo(
        [$author],
        SocialInteractionNotification::class,
        function ($notification) use ($user, $post) {
            return $notification->type === 'like_post'
                && $notification->causer->id === $user->id
                && $notification->model->id === $post->id;
        },
    );
});

it('does not send notification to author if author likes their own post', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);

    Notification::fake();

    $this->action->exec($author, $post);

    Notification::assertNothingSent();
});
