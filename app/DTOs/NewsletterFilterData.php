<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class NewsletterFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?int $category_id = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
    ) {}
}
