<?php

namespace App\DTOs;

namespace App\DTOs;

readonly class NewsletterFilterData
{
    public function __construct(
        public ?string $search = null,
        public ?int $category_id = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
        );
    }
}
