<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\SiteAnalyticsData;
use App\Models\SiteView;
use Illuminate\Support\Facades\DB;

final class GetSiteAnalyticsAction
{
    public function handle(int $days = 30, ?string $startDate = null, ?string $endDate = null): SiteAnalyticsData
    {
        $start = $startDate
            ? now()->parse($startDate)->startOfDay()
            : now()->subDays($days)->startOfDay();

        $end = $endDate
            ? now()->parse($endDate)->endOfDay()
            : now()->endOfDay();

        $stats = SiteView::query()
            ->whereBetween('viewed_at', [$start, $end])
            ->select([
                DB::raw('count(*) as total_views'),
                DB::raw('count(distinct session_id) as unique_visitors'),
                DB::raw('avg(duration) as avg_duration'),
            ])
            ->first();

        $totalViews = (int) ($stats->total_views ?? 0);
        $uniqueVisitors = (int) ($stats->unique_visitors ?? 0);
        $avgDuration = (float) ($stats->avg_duration ?? 0.0);

        $topPages = SiteView::query()
            ->whereBetween('viewed_at', [$start, $end])
            ->select('url', DB::raw('count(*) as total'))
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();

        $topSearches = SiteView::query()
            ->whereBetween('viewed_at', [$start, $end])
            ->whereNotNull('search_query')
            ->select('search_query', DB::raw('count(*) as total'))
            ->groupBy('search_query')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();

        $viewsPerDay = SiteView::query()
            ->whereBetween('viewed_at', [$start, $end])
            ->select(DB::raw('DATE(viewed_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        return new SiteAnalyticsData(
            totalViews: $totalViews,
            uniqueVisitors: $uniqueVisitors,
            avgDuration: round($avgDuration, 2),
            topPages: $topPages,
            topSearches: $topSearches,
            viewsPerDay: $viewsPerDay,
        );
    }
}
