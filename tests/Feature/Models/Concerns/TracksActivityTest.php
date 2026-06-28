<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('uses the most recent of login, last post and account creation', function () {
    Carbon::setTestNow('2026-06-01 12:00:00');

    $user = User::factory()->writer()->create([
        'created_at' => '2026-01-01 00:00:00',
        'last_login_at' => '2026-03-01 00:00:00',
    ]);

    Post::factory()->create([
        'user_id' => $user->id,
        'created_at' => '2026-05-15 00:00:00',
    ]);

    expect($user->lastActivityAt()->toDateString())->toBe('2026-05-15');
});

it('falls back to created_at when there is no login or post', function () {
    $user = User::factory()->create([
        'created_at' => '2026-02-02 00:00:00',
        'last_login_at' => null,
    ]);

    expect($user->lastActivityAt()->toDateString())->toBe('2026-02-02');
});

it('prefers a preloaded posts_max_created_at attribute', function () {
    $user = User::factory()->create([
        'created_at' => '2026-01-01 00:00:00',
        'last_login_at' => '2026-01-05 00:00:00',
    ]);

    $user->setAttribute('posts_max_created_at', '2026-04-20 00:00:00');

    expect($user->lastActivityAt()->toDateString())->toBe('2026-04-20');
});

it('computes inactive days from the last activity', function () {
    Carbon::setTestNow('2026-06-01 00:00:00');

    $user = User::factory()->create([
        'created_at' => '2026-05-22 00:00:00',
        'last_login_at' => '2026-05-22 00:00:00',
    ]);

    expect($user->inactiveDays())->toBe(10);
});
