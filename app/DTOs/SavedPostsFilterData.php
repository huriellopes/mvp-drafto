<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class SavedPostsFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?int $categoryId = null,
        public ?int $collectionId = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
    ) {}
}
