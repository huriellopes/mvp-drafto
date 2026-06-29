<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\SiteView;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final readonly class SiteAnalyticsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private int $days,
        private ?string $startDate = null,
        private ?string $endDate = null,
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
            ->latest('viewed_at');
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
