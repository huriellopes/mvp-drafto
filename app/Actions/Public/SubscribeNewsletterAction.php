<?php

namespace App\Actions\Public;

use App\Models\NewsletterSubscriber;

final class SubscribeNewsletterAction
{
    public function exec(array $data): NewsletterSubscriber
    {
        return NewsletterSubscriber::query()
            ->create($data);
    }
}
