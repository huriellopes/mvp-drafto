<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\DTOs\StoreReportData;
use App\Enums\ReportStatusEnum;
use App\Models\Report;

final class StoreReportAction
{
    public function exec(StoreReportData $data): Report
    {
        return Report::create(array_merge($data->toArray(), [
            'status' => ReportStatusEnum::PENDING,
        ]));
    }
}
