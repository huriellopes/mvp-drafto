<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\DTOs\Public\NewsletterData;
use App\Models\NewsletterSubscriber;

final class SubscribeNewsletterAction
{
    public function exec(NewsletterData $data): void
    {
        NewsletterSubscriber::updateOrCreate(
            ['email' => $data->email],
            ['category_id' => $data->categoryId],
        );
    }
}
