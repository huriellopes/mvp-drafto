<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class FollowersFilterData
{
    public function __construct(
        public ?string $search = null,
        public string $sort = 'followers.created_at',
        public string $direction = 'desc',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            sort: $data['sort'] ?? 'followers.created_at',
            direction: $data['direction'] ?? 'desc',
        );
    }
}
