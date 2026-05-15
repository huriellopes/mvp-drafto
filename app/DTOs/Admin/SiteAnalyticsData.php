<?php

declare(strict_types=1);

namespace App\DTOs\Admin;

use Spatie\LaravelData\Data;

final class SiteAnalyticsData extends Data
{
    public function __construct(
        public int $totalViews,
        public int $uniqueVisitors,
        public float $avgDuration,
        public array $topPages,
        public array $topSearches,
        public array $viewsPerDay,
    ) {}
}
