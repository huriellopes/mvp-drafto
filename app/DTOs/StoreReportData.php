<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ReportReasonEnum;
use Spatie\LaravelData\Data;

class StoreReportData extends Data
{
    public function __construct(
        public int $reportable_id,
        public string $reportable_type,
        public string|ReportReasonEnum $reason,
        public ?string $description = null,
    ) {}
}
