<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class PostViewFilterData
{
    public function __construct(
        public ?string $search = null,
        public string $sort = 'viewed_at',
        public string $direction = 'desc',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            sort: $data['sort'] ?? 'viewed_at',
            direction: $data['direction'] ?? 'desc',
        );
    }
}
