<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\PostView;

it('purges post views older than the retention window and keeps recent ones', function () {
    $old = PostView::factory()->create(['viewed_at' => now()->subDays(40)]);
    $recent = PostView::factory()->create(['viewed_at' => now()->subDays(5)]);

    $this->artisan('app:archive-post-views', ['--days' => 30])
        ->assertExitCode(0);

    expect(PostView::whereKey($old->id)->exists())->toBeFalse()
        ->and(PostView::whereKey($recent->id)->exists())->toBeTrue();
});

it('keeps everything when nothing is older than the cutoff', function () {
    PostView::factory()->count(3)->create(['viewed_at' => now()->subDays(2)]);

    $this->artisan('app:archive-post-views', ['--days' => 90])
        ->assertExitCode(0);

    expect(PostView::count())->toBe(3);
});
