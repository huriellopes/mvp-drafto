<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use RuntimeException;

it('builds the like_comment message', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();
    $causer = User::factory()->withProfile()->create();

    $notification = new SocialInteractionNotification('like_comment', $comment, $causer);

    $data = $notification->toDatabase($user);

    expect($data['type'])->toBe('like_comment')
        ->and($data['message'])->toBeString()
        ->and($data['message'])->not->toBe('');
});

it('builds the mention message and a post link', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $causer = User::factory()->withProfile()->create();

    $notification = new SocialInteractionNotification('mention', $post, $causer);

    $data = $notification->toDatabase($user);

    expect($data['type'])->toBe('mention')
        ->and($data['link'])->toContain($post->slug);
});

it('returns a hash link when route generation throws inside getLink', function () {
    $user = User::factory()->create();
    $causer = User::factory()->withProfile()->create();

    // A model whose slug accessor throws forces the try/catch (lines 71-72) to
    // fall back to "#".
    $explodingModel = new class
    {
        public function __get($name)
        {
            throw new RuntimeException('boom');
        }
    };

    $notification = new SocialInteractionNotification('mention', $explodingModel, $causer);

    $data = $notification->toDatabase($user);

    expect($data['link'])->toBe('#');
});
