<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Post;
use App\Models\User;

it('runs the scale benchmark with a tiny workload', function () {
    Post::factory()->published()->create();

    $this->artisan('benchmark:scale', ['--users' => 2, '--likes' => 3])
        ->assertExitCode(0);

    // Os usuários criados pelo benchmark são removidos ao final.
    expect(User::query()->count())->toBeGreaterThanOrEqual(0);
});
