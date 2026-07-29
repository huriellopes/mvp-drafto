<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Widgets;

use App\Livewire\Dashboard\Widgets\RecentActivity;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

/**
 * Covers the guest guard of items() (lines 26-27): no authenticated user
 * short-circuits to an empty collection. We invoke the computed directly because
 * the widget's view assumes an authenticated user and is never rendered for a
 * guest in production.
 */
it('returns an empty collection for a guest', function () {
    $component = new RecentActivity;

    expect($component->items())->toBeEmpty();
});
