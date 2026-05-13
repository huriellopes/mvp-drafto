<?php

declare(strict_types=1);

namespace App\Notifications;

use App\DTOs\SupportContactData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportMessageReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public SupportContactData $data
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[Support] {$this->data->subject}")
            ->greeting("Hello, Team Drafto!")
            ->line("You have received a new support message from {$this->data->name} ({$this->data->email}).")
            ->line("Subject: {$this->data->subject}")
            ->line("Message:")
            ->line($this->data->message)
            ->line("Please respond to the user as soon as possible.")
            ->action('View Support Dashboard', url('/dashboard/admin/reports')) // Or a specific support view if exists
            ->line('Thank you for using our platform!');
    }
}
