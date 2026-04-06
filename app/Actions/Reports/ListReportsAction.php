<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\DTOs\ReportFilterData;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListReportsAction
{
    public function exec(ReportFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Report::query()
            ->with(['reporter.profile', 'reviewer.profile', 'reportable'])
            ->when($filters->search, function ($query, $search) {
                $query->whereHas('reporter', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($filters->status, fn($q) => $q->where('status', $filters->status))
            ->when($filters->reason, fn($q) => $q->where('reason', $filters->reason))
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($perPage);
    }
}
