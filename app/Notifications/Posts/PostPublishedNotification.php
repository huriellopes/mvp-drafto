<?php

declare(strict_types=1);

namespace App\Notifications\Posts;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Post $post,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('🎉 Seu post foi publicado!')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Temos o prazer de informar que o seu post \"{$this->post->title}\" que estava agendado, acaba de ser publicado com sucesso.")
            ->action('Ver meu post', route('posts.show', $this->post->slug))
            ->line('Obrigado por compartilhar seu conhecimento no Drafto!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'title' => $this->post->title,
            'message' => 'Seu post agendado foi publicado com sucesso!',
            'type' => 'post_published',
        ];
    }
}
