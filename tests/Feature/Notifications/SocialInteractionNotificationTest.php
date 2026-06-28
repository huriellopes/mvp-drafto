<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Post;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('sends the social interaction notification via database and mail', function () {
    $user = User::factory()->withProfile()->create();
    $causer = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create();

    $notification = new SocialInteractionNotification('like_post', $post, $causer);

    expect($notification->via($user))->toBe(['database', 'mail']);
});

it('exposes a like_post interaction as a database payload', function () {
    $user = User::factory()->withProfile()->create();
    $causer = User::factory()->withProfile()->create(['name' => 'Carlos']);
    $post = Post::factory()->published()->create(['title' => 'A Obra']);

    $notification = new SocialInteractionNotification('like_post', $post, $causer);
    $data = $notification->toDatabase($user);

    expect($data['type'])->toBe('like_post')
        ->and($data['causer_name'])->toBe('Carlos')
        ->and($data['message'])->toBe(__('notifications.social.messages.like_post', ['title' => 'A Obra']))
        ->and($data['link'])->toBe(route('posts.show', $post->slug));
});

it('builds the like_post interaction mail message', function () {
    $user = User::factory()->withProfile()->create();
    $causer = User::factory()->withProfile()->create(['name' => 'Carlos']);
    $post = Post::factory()->published()->create(['title' => 'A Obra']);

    $notification = new SocialInteractionNotification('like_post', $post, $causer);
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe(__('notifications.social.subject', ['name' => 'Carlos']))
        ->and($mail->actionText)->toBe(__('notifications.social.action'));
});

it('handles a follow interaction link via the causer profile', function () {
    $user = User::factory()->withProfile()->create();
    $causer = User::factory()->withProfile()->create();

    $notification = new SocialInteractionNotification('follow', null, $causer);
    $data = $notification->toDatabase($user);

    expect($data['type'])->toBe('follow')
        ->and($data['message'])->toBe(__('notifications.social.messages.follow'))
        ->and($data['link'])->toBe(route('profile.show', $causer->profile->username));
});

it('falls back gracefully for unknown interaction types', function () {
    $user = User::factory()->withProfile()->create();

    $notification = new SocialInteractionNotification('unknown', null, null);
    $data = $notification->toDatabase($user);

    expect($data['causer_name'])->toBe('Usuário')
        ->and($data['message'])->toBe(__('notifications.social.messages.default'))
        ->and($data['link'])->toBe('#');
});
