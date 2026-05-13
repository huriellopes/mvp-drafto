<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use Spatie\LaravelData\Data;

final class PostFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $category = null,
        public ?string $tag = null,
        public ?string $type = null,
        public string $sort = 'latest',
        public int $perPage = 12,
    ) {}
}
