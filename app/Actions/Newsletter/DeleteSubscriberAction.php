<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Models\NewsletterSubscriber;

final class DeleteSubscriberAction
{
    public function exec(NewsletterSubscriber $subscriber): void
    {
        $subscriber->delete();
    }
}
