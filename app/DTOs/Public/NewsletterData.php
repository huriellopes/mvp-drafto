<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use Spatie\LaravelData\Data;

final class NewsletterData extends Data
{
    public function __construct(
        public string $email,
        public ?int $categoryId = null,
    ) {}
}
