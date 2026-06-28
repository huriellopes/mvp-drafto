<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\SiteView;
use App\Models\User;

it('seeds fake analytics records into site_views', function () {
    // O comando referencia user_id=1, então garantimos que ele exista (FK).
    User::factory()->create(['id' => 1]);

    expect(SiteView::count())->toBe(0);

    $this->artisan('app:seed-fake-analytics')
        ->assertExitCode(0);

    expect(SiteView::count())->toBeGreaterThan(0);
});
