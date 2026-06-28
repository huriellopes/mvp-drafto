<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\DTOs\Public\StoreSiteViewData;
use App\Jobs\ProcessSiteViewJob;
use App\Models\SiteView;
use App\Models\User;

it('stores a site view row through the action', function (): void {
    $user = User::factory()->create();

    $data = new StoreSiteViewData(
        userId: $user->id,
        url: 'https://drafto.pro/explore',
        ipAddress: '127.0.0.1',
        userAgent: 'PHPUnit',
        sessionId: 'session-abc',
        searchQuery: 'laravel',
        duration: 42,
    );

    app()->call([new ProcessSiteViewJob($data), 'handle']);

    expect(SiteView::count())->toBe(1);

    $view = SiteView::first();

    expect($view->user_id)->toBe($user->id)
        ->and($view->url)->toBe('https://drafto.pro/explore')
        ->and($view->ip_address)->toBe('127.0.0.1')
        ->and($view->session_id)->toBe('session-abc')
        ->and($view->search_query)->toBe('laravel')
        ->and($view->duration)->toBe(42)
        ->and($view->viewed_at)->not->toBeNull();
});

it('stores a site view for a guest (no user)', function (): void {
    $data = new StoreSiteViewData(
        userId: null,
        url: 'https://drafto.pro/',
        ipAddress: null,
        userAgent: null,
        sessionId: null,
        searchQuery: null,
    );

    app()->call([new ProcessSiteViewJob($data), 'handle']);

    expect(SiteView::count())->toBe(1)
        ->and(SiteView::first()->user_id)->toBeNull()
        ->and(SiteView::first()->duration)->toBe(0);
});

it('truncates an overly long user agent to 1000 chars', function (): void {
    $data = new StoreSiteViewData(
        userId: null,
        url: 'https://drafto.pro/',
        ipAddress: null,
        userAgent: str_repeat('a', 2000),
        sessionId: null,
        searchQuery: null,
    );

    app()->call([new ProcessSiteViewJob($data), 'handle']);

    expect(mb_strlen((string) SiteView::first()->user_agent))->toBe(1000);
});
