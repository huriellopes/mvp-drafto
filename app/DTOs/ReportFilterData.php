<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ReportFilterData
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $reason = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: $data['status'] ?? null,
            reason: $data['reason'] ?? null,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
        );
    }
}
