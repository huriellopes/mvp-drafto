<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PlatformUpdate;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ProductUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $unsubscribeUrl;

    public function __construct(
        public User $user,
        public PlatformUpdate $update,
    ) {
        $this->unsubscribeUrl = URL::signedRoute(
            'email.preferences.unsubscribe',
            ['user' => $this->user->id, 'type' => 'product_updates'],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Novidades no Drafto 🚀');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.product-update',
            with: [
                'user' => $this->user,
                'update' => $this->update,
                'ctaUrl' => route('dashboard.index'),
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }
}
