<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Admin;

use App\Actions\Admin\GetSiteAnalyticsAction;
use App\DTOs\Admin\SiteAnalyticsData;
use App\Models\SiteView;

beforeEach(function () {
    $this->action = app(GetSiteAnalyticsAction::class);
});

it('aggregates total views, unique visitors and average duration within the window', function () {
    SiteView::create([
        'url' => '/a',
        'session_id' => 'sess-1',
        'duration' => 10,
        'viewed_at' => now()->subDay(),
    ]);
    SiteView::create([
        'url' => '/a',
        'session_id' => 'sess-1',
        'duration' => 20,
        'viewed_at' => now()->subDay(),
    ]);
    SiteView::create([
        'url' => '/b',
        'session_id' => 'sess-2',
        'duration' => 30,
        'viewed_at' => now()->subDay(),
    ]);

    $result = $this->action->handle(days: 30);

    expect($result)->toBeInstanceOf(SiteAnalyticsData::class)
        ->and($result->totalViews)->toBe(3)
        ->and($result->uniqueVisitors)->toBe(2)
        ->and($result->avgDuration)->toBe(20.0);
});

it('builds top pages ordered by visit count', function () {
    SiteView::create(['url' => '/popular', 'session_id' => 's1', 'viewed_at' => now()]);
    SiteView::create(['url' => '/popular', 'session_id' => 's2', 'viewed_at' => now()]);
    SiteView::create(['url' => '/rare', 'session_id' => 's3', 'viewed_at' => now()]);

    $result = $this->action->handle();

    expect($result->topPages[0]['url'])->toBe('/popular')
        ->and((int) $result->topPages[0]['total'])->toBe(2);
});

it('collects top searches and views per day', function () {
    SiteView::create(['url' => '/s', 'session_id' => 's1', 'search_query' => 'laravel', 'viewed_at' => now()]);
    SiteView::create(['url' => '/s', 'session_id' => 's2', 'search_query' => 'laravel', 'viewed_at' => now()]);
    SiteView::create(['url' => '/s', 'session_id' => 's3', 'viewed_at' => now()]);

    $result = $this->action->handle();

    expect($result->topSearches[0]['search_query'])->toBe('laravel')
        ->and((int) $result->topSearches[0]['total'])->toBe(2)
        ->and($result->viewsPerDay)->toHaveKey(now()->toDateString());
});

it('respects an explicit start and end date range', function () {
    SiteView::create(['url' => '/in', 'session_id' => 's1', 'viewed_at' => now()->subDays(2)]);
    SiteView::create(['url' => '/out', 'session_id' => 's2', 'viewed_at' => now()->subDays(40)]);

    $result = $this->action->handle(
        startDate: now()->subDays(5)->toDateString(),
        endDate: now()->toDateString(),
    );

    expect($result->totalViews)->toBe(1);
});

it('returns zeroed analytics when there are no views', function () {
    $result = $this->action->handle();

    expect($result->totalViews)->toBe(0)
        ->and($result->uniqueVisitors)->toBe(0)
        ->and($result->avgDuration)->toBe(0.0)
        ->and($result->topPages)->toBe([]);
});
