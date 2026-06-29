<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Post;
use App\Models\User;

it('generates the sitemap file successfully', function () {
    User::factory()->writer()->withProfile()->create();
    Post::factory()->published()->public()->create();

    $path = public_path('sitemap.xml');
    @unlink($path);

    $this->artisan('seo:generate-sitemap')
        ->assertExitCode(0);

    expect(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('<urlset');

    @unlink($path);
});
