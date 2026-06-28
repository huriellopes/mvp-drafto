<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\NewsletterNotification;
use App\Models\NewsletterSubscriber;

it('builds the newsletter mail with posts', function () {
    $subscriber = NewsletterSubscriber::factory()->verified()->create();

    $posts = [
        ['slug' => 'meu-post', 'title' => 'Meu Post', 'excerpt' => 'Um resumo'],
    ];

    $mail = new NewsletterNotification($posts, 'Tecnologia', $subscriber);

    $mail->assertHasSubject(__('notifications.newsletter.subject', ['category' => 'Tecnologia']));

    $html = $mail->render();

    expect($html)
        ->toContain('Meu Post')
        ->toContain(route('posts.show', 'meu-post'));

    // The signed unsubscribe URL is generated in the constructor.
    expect($mail->unsubscribeUrl)->toContain('/newsletter/unsubscribe/');
});

it('uses the important subject and shows a custom message', function () {
    $subscriber = NewsletterSubscriber::factory()->verified()->create();

    $mail = new NewsletterNotification([], 'Tecnologia', $subscriber, 'Aviso importante');

    $mail->assertHasSubject(__('notifications.newsletter.subject_important', ['app' => config('app.name')]));

    expect($mail->render())->toContain('Aviso importante');
});
