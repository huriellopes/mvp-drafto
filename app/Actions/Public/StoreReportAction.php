<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Enums\ReportStatusEnum;
use App\Models\Report;

final class StoreReportAction
{
    public function exec(array $data): Report
    {
        return Report::create(array_merge($data, [
            'status' => ReportStatusEnum::PENDING,
        ]));
    }
}
