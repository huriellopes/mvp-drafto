<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PostStatusEnum;

readonly class PostFiltersDTO
{
    public function __construct(
        public ?string $search = null,
        public ?PostStatusEnum $status = null,
        public ?PostStatusEnum $notStatus = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
        public int $perPage = 10,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: $data['status'] ?? null,
            notStatus: $data['notStatus'] ?? null,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
            perPage: (int) ($data['perPage'] ?? 10),
        );
    }
}
