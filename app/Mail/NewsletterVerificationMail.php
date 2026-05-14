<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class NewsletterVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: Lang::get('notifications.newsletter.verification.subject'),
        );
    }

    public function content(): Content
    {
        $verificationUrl = route('newsletter.verify', [
            'email' => $this->subscriber->email,
            'token' => $this->subscriber->verification_token,
        ]);

        return new Content(
            view: 'emails.newsletter.verification',
            with: [
                'verificationUrl' => $verificationUrl,
            ],
        );
    }
}
