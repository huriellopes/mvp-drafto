<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class SavedPostsFilterData
{
    public function __construct(
        public ?string $search = null,
        public ?int $categoryId = null,
        public ?int $collectionId = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            categoryId: isset($data['categoryId']) ? (int) $data['categoryId'] : null,
            collectionId: isset($data['collectionId']) ? (int) $data['collectionId'] : null,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
        );
    }
}
