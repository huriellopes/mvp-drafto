<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Jobs\SendNewsletterJob;
use App\Models\NewsletterSubscriber;

final class SendBroadcastAction
{
    /**
     * Dispara um comunicado manual para todos os inscritos que aceitam novidades da plataforma.
     */
    public function exec(string $message, string $subject = 'Novidade na Plataforma'): void
    {
        NewsletterSubscriber::query()
            ->whereNotNull('verified_at')
            ->where('receive_platform_updates', true)
            ->chunk(100, function ($subscribers) use ($message, $subject) {
                foreach ($subscribers as $subscriber) {
                    dispatch(new SendNewsletterJob(
                        $subscriber,
                        [],
                        // Sem posts específicos
                        $subject,
                        $message,
                    ));
                }
            });
    }
}
