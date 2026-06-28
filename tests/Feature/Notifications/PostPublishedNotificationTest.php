<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Post;
use App\Models\User;
use App\Notifications\Posts\PostPublishedNotification;
use Illuminate\Notifications\Messages\MailMessage;

it('sends the post published notification via mail and database', function () {
    $user = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create();

    $notification = new PostPublishedNotification($post);

    expect($notification->via($user))->toBe(['mail', 'database']);
});

it('builds the post published mail message', function () {
    $user = User::factory()->withProfile()->create(['name' => 'Maria']);
    $post = Post::factory()->published()->create(['title' => 'Meu Post']);

    $notification = new PostPublishedNotification($post);
    $mail = $notification->toMail($user);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe('🎉 Seu post foi publicado!')
        ->and($mail->greeting)->toBe('Olá, Maria!')
        ->and($mail->actionText)->toBe('Ver meu post')
        ->and($mail->actionUrl)->toBe(route('posts.show', $post->slug));
});

it('exposes the post published payload as an array', function () {
    $user = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create(['title' => 'Meu Post']);

    $notification = new PostPublishedNotification($post);

    expect($notification->toArray($user))->toBe([
        'post_id' => $post->id,
        'title' => 'Meu Post',
        'message' => 'Seu post agendado foi publicado com sucesso!',
        'type' => 'post_published',
    ]);
});
