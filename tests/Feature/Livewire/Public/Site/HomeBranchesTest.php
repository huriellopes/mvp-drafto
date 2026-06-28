<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public\Site;

use App\Livewire\Public\Site\Home;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

it('recovers when the featured writers cache holds a corrupt value', function () {
    // Simula um valor corrompido em cache (não é uma Collection). O render
    // detecta, faz Cache::forget e recalcula — não deve quebrar a página.
    Cache::put('home_featured_writers_v2', 'corrupted', now()->addMinutes(30));

    // Home é #[Lazy]; $refresh dispara o render real onde está o fallback.
    Livewire::test(Home::class)->call('$refresh')->assertOk();

    expect(Cache::get('home_featured_writers_v2'))
        ->toBeInstanceOf(Collection::class);
});

it('recovers when the categories cache holds a corrupt value', function () {
    Cache::put('home_categories_v2', 'corrupted', now()->addMinutes(60));

    Livewire::test(Home::class)->call('$refresh')->assertOk();

    expect(Cache::get('home_categories_v2'))
        ->toBeInstanceOf(Collection::class);
});
