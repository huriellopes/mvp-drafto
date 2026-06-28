<?php

declare(strict_types=1);

namespace Tests\Feature\Exports;

use App\Exports\SiteAnalyticsExport;
use App\Models\SiteView;

it('exposes the expected headings', function () {
    $export = new SiteAnalyticsExport(7);

    expect($export->headings())->toBe([
        'ID',
        'URL',
        'Referer',
        'IP Address',
        'User Agent',
        'Duration (sec)',
        'Search Query',
        'Viewed At',
    ]);
});

it('queries views within the trailing-days window', function () {
    SiteView::create([
        'url' => '/recent',
        'ip_address' => '127.0.0.1',
        'viewed_at' => now()->subDay(),
    ]);
    SiteView::create([
        'url' => '/old',
        'ip_address' => '127.0.0.1',
        'viewed_at' => now()->subDays(30),
    ]);

    $export = new SiteAnalyticsExport(7);

    expect($export->query()->count())->toBe(1)
        ->and($export->query()->first()->url)->toBe('/recent');
});

it('queries views within an explicit start/end date range', function () {
    SiteView::create([
        'url' => '/in-range',
        'ip_address' => '127.0.0.1',
        'viewed_at' => now()->subDays(3),
    ]);
    SiteView::create([
        'url' => '/out-of-range',
        'ip_address' => '127.0.0.1',
        'viewed_at' => now()->subDays(20),
    ]);

    $export = new SiteAnalyticsExport(
        days: 7,
        startDate: now()->subDays(5)->toDateString(),
        endDate: now()->toDateString(),
    );

    expect($export->query()->count())->toBe(1)
        ->and($export->query()->first()->url)->toBe('/in-range');
});

it('maps a row', function () {
    SiteView::create([
        'url' => '/page',
        'ip_address' => '8.8.8.8',
        'user_agent' => 'Bot',
        'duration' => 42,
        'search_query' => 'laravel',
        'viewed_at' => now(),
    ]);

    $export = new SiteAnalyticsExport(7);
    $row = $export->map($export->query()->first());

    // The site_views table has no "referer" column, so that cell is always null.
    expect($row)->toHaveCount(8)
        ->and($row[1])->toBe('/page')
        ->and($row[2])->toBeNull()
        ->and($row[3])->toBe('8.8.8.8')
        ->and($row[4])->toBe('Bot')
        ->and($row[5])->toBe(42)
        ->and($row[6])->toBe('laravel');
});
