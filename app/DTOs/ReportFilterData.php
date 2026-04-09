<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class ReportFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $reason = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
    ) {}
}
