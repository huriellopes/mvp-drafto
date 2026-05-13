<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class PostViewFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public string $sort = 'viewed_at',
        public string $direction = 'desc',
    ) {}
}
