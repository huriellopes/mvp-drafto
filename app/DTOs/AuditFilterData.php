<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class AuditFilterData extends Data
{
    public function __construct(
        public ?int $userId = null,
        public ?string $event = null,
        public ?string $auditableType = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public int $perPage = 20,
    ) {}
}
