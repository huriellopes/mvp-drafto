<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\NewsletterNotification;
use App\Models\NewsletterSubscriber;

it('renders translated copy and a working unsubscribe link in the newsletter body', function () {
    app()->setLocale('pt_BR');

    $subscriber = NewsletterSubscriber::factory()->verified()->create();
    $mail = new NewsletterNotification([], 'Geral', $subscriber);

    $html = $mail->render();

    expect($html)
        ->toContain('Gerenciar inscrições')        // notifications.newsletter.unsubscribe (pt_BR)
        ->toContain($mail->unsubscribeUrl)          // :url interpolado no corpo
        ->not->toContain('notifications.newsletter.'); // nenhuma chave de tradução vazando
});
