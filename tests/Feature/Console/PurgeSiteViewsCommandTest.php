<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\SiteView;

it('purges site views older than the retention window', function () {
    $old = SiteView::create([
        'url' => 'http://localhost/old',
        'viewed_at' => now()->subDays(120),
    ]);
    $recent = SiteView::create([
        'url' => 'http://localhost/recent',
        'viewed_at' => now()->subDays(10),
    ]);

    $this->artisan('app:purge-site-views', ['--days' => 90])
        ->assertExitCode(0);

    expect(SiteView::whereKey($old->id)->exists())->toBeFalse()
        ->and(SiteView::whereKey($recent->id)->exists())->toBeTrue();
});
