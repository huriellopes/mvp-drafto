<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewsletterNotification;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected NewsletterSubscriber $subscriber,
        protected array $posts = [],
        protected string $categoryName = 'Informativo',
        protected ?string $customMessage = null,
    ) {}

    public function handle(): void
    {
        Mail::to($this->subscriber->email)->send(
            new NewsletterNotification(
                $this->posts,
                $this->categoryName,
                $this->subscriber,
                $this->customMessage,
            ),
        );
    }
}
