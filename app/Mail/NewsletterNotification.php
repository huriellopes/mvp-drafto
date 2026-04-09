<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class NewsletterNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $unsubscribeUrl;

    public function __construct(
        public array $posts,
        public string $categoryName,
        public NewsletterSubscriber $subscriber,
        public ?string $customMessage = null,
    ) {
        $this->unsubscribeUrl = URL::temporarySignedRoute(
            'newsletter.unsubscribe',
            now()->addDays(30),
            ['email' => $this->subscriber->email],
        );
    }

    public function build()
    {
        return $this->subject($this->customMessage ? 'Informativo: ' . config('app.name') : "Novidades na Drafto: {$this->categoryName}")
            ->view('emails.newsletter.posts', [
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]);
    }
}
