<?php

declare(strict_types=1);

namespace App\Notifications;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

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
            ->subject(Lang::get('notifications.social.subject', ['name' => $causerName]))
            ->line($this->getMessage())
            ->action(Lang::get('notifications.social.action'), url($this->getLink()))
            ->line(Lang::get('notifications.social.thanks'));
    }

    protected function getMessage(): string
    {
        return match ($this->type) {
            'like_post' => Lang::get('notifications.social.messages.like_post', ['title' => ($this->model?->title ?? 'Obra')]),
            'like_comment' => Lang::get('notifications.social.messages.like_comment'),
            'mention' => Lang::get('notifications.social.messages.mention'),
            'follow' => Lang::get('notifications.social.messages.follow'),
            default => Lang::get('notifications.social.messages.default'),
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
