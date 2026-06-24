<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Models\SiteView;

it('purges site views older than the retention window and keeps recent ones', function () {
    $old = SiteView::create([
        'url' => 'http://localhost/old',
        'ip_address' => '127.0.0.1',
        'session_id' => 'old-session',
        'duration' => 10,
        'viewed_at' => now()->subDays(120),
    ]);

    $recent = SiteView::create([
        'url' => 'http://localhost/recent',
        'ip_address' => '127.0.0.1',
        'session_id' => 'recent-session',
        'duration' => 10,
        'viewed_at' => now()->subDays(5),
    ]);

    $this->artisan('app:purge-site-views', ['--days' => 90])
        ->assertSuccessful();

    $this->assertDatabaseMissing('site_views', ['id' => $old->id]);
    $this->assertDatabaseHas('site_views', ['id' => $recent->id]);
});
