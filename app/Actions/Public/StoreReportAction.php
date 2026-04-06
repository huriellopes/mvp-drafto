<?php

namespace App\Actions\Public;

use App\Models\Report;
use App\Enums\ReportStatusEnum;

final class StoreReportAction
{
    public function exec(array $data): Report
    {
        return Report::create(array_merge($data, [
            'status' => ReportStatusEnum::PENDING
        ]));
    }
}
