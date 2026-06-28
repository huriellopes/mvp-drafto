<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Public;

use App\Actions\Public\UpdateSiteViewDurationAction;
use App\Models\SiteView;

beforeEach(function () {
    $this->action = app(UpdateSiteViewDurationAction::class);
});

it('updates the duration of the most recent view for the session and url', function () {
    $older = SiteView::create([
        'session_id' => 'sess-1',
        'url' => '/page',
        'duration' => 0,
        'viewed_at' => now()->subMinutes(10),
    ]);
    $latest = SiteView::create([
        'session_id' => 'sess-1',
        'url' => '/page',
        'duration' => 0,
        'viewed_at' => now(),
    ]);

    $this->action->handle('sess-1', '/page', 42);

    expect($latest->fresh()->duration)->toBe(42)
        ->and($older->fresh()->duration)->toBe(0);
});

it('does nothing when there is no matching view', function () {
    $this->action->handle('unknown', '/missing', 99);

    expect(SiteView::count())->toBe(0);
});
