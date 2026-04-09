<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class ModuleFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public string $sortField = 'name',
        public string $sortDirection = 'asc',
        public int $perPage = 10,
    ) {}
}
