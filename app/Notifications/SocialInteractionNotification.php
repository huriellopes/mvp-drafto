<?php

declare(strict_types=1);

namespace App\Notifications;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class SocialInteractionNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

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
            'causer_name' => $this->causer?->name ?? 'Usuário',
            'causer_avatar' => $this->causer?->profile?->avatar_path,
            'message' => $this->getMessage(),
            'link' => $this->getLink(),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $causerName = $this->causer?->name ?? 'Alguém';

        return (new MailMessage())
            ->subject('Nova interação na Drafto: ' . $causerName)
            ->line($this->getMessage())
            ->action('Ver no Site', url($this->getLink()))
            ->line('Obrigado por fazer parte da nossa comunidade!');
    }

    protected function getMessage(): string
    {
        return match ($this->type) {
            'like_post' => 'curtiu seu post: ' . ($this->model?->title ?? 'Obra'),
            'like_comment' => 'curtiu seu comentário',
            'mention' => 'mencionou você em um comentário',
            'follow' => 'começou a seguir você',
            default => 'interagiu com você',
        };
    }

    protected function getLink(): string
    {
        try {
            return match ($this->type) {
                'like_post', 'mention' => route('posts.show', $this->model?->slug ?? $this->model?->post?->slug ?? '#'),
                'follow' => $this->causer?->profile?->username ? route('profile.show', $this->causer->profile->username) : '#',
                default => '#',
            };
        } catch (Exception $e) {
            return '#';
        }
    }
}
