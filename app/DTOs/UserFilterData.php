<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UserFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $role = null,
        public ?string $status = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
        public int $per_page = 15,
    ) {}

    public function getCacheKey(): string
    {
        return 'users_list_' . sha1(serialize($this->toArray()));
    }
}
