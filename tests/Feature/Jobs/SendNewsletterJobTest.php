<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SendNewsletterJob;
use App\Mail\NewsletterNotification;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

it('sends the newsletter mail to the subscriber email', function (): void {
    $subscriber = NewsletterSubscriber::factory()->verified()->create([
        'email' => 'reader@example.com',
    ]);

    app()->call([new SendNewsletterJob($subscriber, [], 'Tecnologia', 'Olá!'), 'handle']);

    Mail::assertQueued(NewsletterNotification::class, function (NewsletterNotification $mail): bool {
        return $mail->hasTo('reader@example.com');
    });
});

it('sends exactly one newsletter mail', function (): void {
    $subscriber = NewsletterSubscriber::factory()->verified()->create();

    app()->call([new SendNewsletterJob($subscriber), 'handle']);

    Mail::assertQueued(NewsletterNotification::class, 1);
});
