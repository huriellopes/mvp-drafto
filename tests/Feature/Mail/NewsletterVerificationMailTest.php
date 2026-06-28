<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscriber;

it('builds the newsletter verification mail', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $mail = new NewsletterVerificationMail($subscriber);

    $mail->assertHasSubject(__('notifications.newsletter.verification.subject'));

    expect($mail->render())
        ->toContain('/newsletter/verify')
        ->toContain($subscriber->verification_token);
});
