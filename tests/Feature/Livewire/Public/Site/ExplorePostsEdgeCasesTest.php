<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public\Site;

use App\Livewire\Public\Site\ExplorePosts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

/**
 * Covers the categories() cache-fallback branch (lines 74-77): a corrupt
 * (non-Collection) cached value is forgotten and recomputed.
 */
it('recovers when the explore categories cache holds a corrupt value', function () {
    Cache::put('explore_categories_v1', 'corrupted', now()->addHours(1));

    $categories = Livewire::test(ExplorePosts::class)->get('categories');

    expect($categories)->toBeInstanceOf(Collection::class);
});

/**
 * Covers the tags() cache-fallback branch (lines 96-99).
 */
it('recovers when the explore tags cache holds a corrupt value', function () {
    Cache::put('explore_tags_v1', 'corrupted', now()->addHours(1));

    $tags = Livewire::test(ExplorePosts::class)->get('tags');

    expect($tags)->toBeInstanceOf(Collection::class);
});
