<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class UserFilterData
{
    public function __construct(
        public ?string $search = null,
        public ?string $role = null,
        public ?string $status = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
        public int $per_page = 15
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            role: $data['role'] ?? null,
            status: $data['status'] ?? null,
            sort: $data['sort'] ?? 'created_at',
            direction: $data['direction'] ?? 'desc',
            per_page: (int) ($data['per_page'] ?? 15),
        );
    }

    public function getCacheKey(): string
    {
        return 'users_list_' . sha1(serialize([
                $this->search,
                $this->role,
                $this->status,
                $this->sort,
                $this->direction,
                $this->per_page
            ]));
    }
}
