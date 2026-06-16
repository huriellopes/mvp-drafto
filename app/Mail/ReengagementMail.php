<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ReengagementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $unsubscribeUrl;

    public function __construct(
        public User $user,
        public int $stage = 0,
        public int $inactiveDays = 0,
    ) {
        $this->unsubscribeUrl = URL::signedRoute(
            'email.preferences.unsubscribe',
            ['user' => $this->user->id, 'type' => 'reengagement'],
        );
    }

    public function envelope(): Envelope
    {
        $subject = match (true) {
            $this->stage >= 60 => 'Suas ideias têm espaço no Drafto',
            $this->stage >= 30 => 'Que tal voltar a escrever no Drafto?',
            default => 'Sentimos sua falta no Drafto 👋',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reengagement',
            with: [
                'user' => $this->user,
                'inactiveDays' => $this->inactiveDays,
                'ctaUrl' => route('dashboard.posts.create'),
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }
}
