<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\Admin\SiteAnalyticsData;
use App\Models\SiteView;
use Illuminate\Support\Facades\DB;

final class GetSiteAnalyticsAction
{
    public function handle(int $days = 30): SiteAnalyticsData
    {
        $startDate = now()->subDays($days);

        $totalViews = SiteView::query()
            ->where('viewed_at', '>=', $startDate)
            ->count();

        $uniqueVisitors = SiteView::query()
            ->where('viewed_at', '>=', $startDate)
            ->distinct('session_id')
            ->count();

        $avgDuration = (float) SiteView::query()
            ->where('viewed_at', '>=', $startDate)
            ->avg('duration') ?? 0.0;

        $topPages = SiteView::query()
            ->where('viewed_at', '>=', $startDate)
            ->select('url', DB::raw('count(*) as total'))
            ->groupBy('url')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();

        $topSearches = SiteView::query()
            ->where('viewed_at', '>=', $startDate)
            ->whereNotNull('search_query')
            ->select('search_query', DB::raw('count(*) as total'))
            ->groupBy('search_query')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->toArray();

        $viewsPerDay = SiteView::query()
            ->where('viewed_at', '>=', $startDate)
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
