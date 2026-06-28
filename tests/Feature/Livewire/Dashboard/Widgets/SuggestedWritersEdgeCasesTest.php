<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Widgets;

use App\Livewire\Dashboard\Widgets\SuggestedWriters;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

/**
 * Covers the guest guard of suggestions() (lines 26-27): no authenticated user
 * id short-circuits to an empty collection.
 */
it('returns an empty collection for a guest', function () {
    $suggestions = Livewire::test(SuggestedWriters::class)->get('suggestions');

    expect($suggestions)->toBeEmpty();
});
