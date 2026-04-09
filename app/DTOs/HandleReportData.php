<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ReportStatusEnum;
use Spatie\LaravelData\Data;

final class HandleReportData extends Data
{
    public function __construct(
        public int $reportId,
        public ReportStatusEnum $status,
        public string $feedback,
        public bool $shouldBanUser = false,
        public ?string $banReason = null,
        public int $banDays = 30,
    ) {}
}
