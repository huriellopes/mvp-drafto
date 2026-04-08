<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ModuleFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public string $sortField = 'name',
        public string $sortDirection = 'asc',
        public int $perPage = 10,
    ) {}
}
