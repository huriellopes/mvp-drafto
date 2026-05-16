<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\SiteView;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class SiteAnalyticsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly int $days,
        private readonly ?string $startDate = null,
        private readonly ?string $endDate = null,
    ) {}

    public function query()
    {
        $start = $this->startDate
            ? now()->parse($this->startDate)->startOfDay()
            : now()->subDays($this->days)->startOfDay();

        $end = $this->endDate
            ? now()->parse($this->endDate)->endOfDay()
            : now()->endOfDay();

        return SiteView::query()
            ->whereBetween('viewed_at', [$start, $end])
            ->orderByDesc('viewed_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'URL',
            'Referer',
            'IP Address',
            'User Agent',
            'Duration (sec)',
            'Search Query',
            'Viewed At',
        ];
    }

    public function map($view): array
    {
        return [
            $view->id,
            $view->url,
            $view->referer,
            $view->ip_address,
            $view->user_agent,
            $view->duration,
            $view->search_query,
            $view->viewed_at->format('d/m/Y H:i:s'),
        ];
    }
}
