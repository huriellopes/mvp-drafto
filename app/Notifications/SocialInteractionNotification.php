<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SocialInteractionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public mixed $model,
        public $causer,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => $this->type,
            'causer_name' => $this->causer->name,
            'causer_avatar' => $this->causer->profile->avatar_path,
            'message' => $this->getMessage(),
            'link' => $this->getLink(),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Nova interação na Drafto: ' . $this->causer->name)
            ->line($this->getMessage())
            ->action('Ver no Site', url($this->getLink()))
            ->line('Obrigado por fazer parte da nossa comunidade!');
    }

    protected function getMessage(): string
    {
        return match ($this->type) {
            'like_post' => "curtiu seu post: {$this->model->title}",
            'like_comment' => 'curtiu seu comentário',
            'mention' => 'mencionou você em um comentário',
            'follow' => 'começou a seguir você',
            default => 'interagiu com você',
        };
    }

    protected function getLink(): string
    {
        return match ($this->type) {
            'like_post', 'mention' => route('posts.show', $this->model->slug ?? $this->model->post->slug),
            'follow' => route('profile.show', $this->causer->profile->username),
            default => '#',
        };
    }
}
