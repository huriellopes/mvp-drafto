<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use App\Models\User;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('never reports expiration for a verified user', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'created_at' => now()->subDays(60),
    ]);

    expect($user->hasVerificationExpired())->toBeFalse();
});

it('reports expiration after the 15-day window for an unverified user', function () {
    Carbon::setTestNow('2026-06-20 00:00:00');

    $user = User::factory()->unverified()->create([
        'created_at' => '2026-06-01 00:00:00',
    ]);

    expect($user->hasVerificationExpired())->toBeTrue();
});

it('does not report expiration within the 15-day window', function () {
    Carbon::setTestNow('2026-06-10 00:00:00');

    $user = User::factory()->unverified()->create([
        'created_at' => '2026-06-01 00:00:00',
    ]);

    expect($user->hasVerificationExpired())->toBeFalse();
});

it('computes days left to verify within the window', function () {
    Carbon::setTestNow('2026-06-06 00:00:00');

    $user = User::factory()->unverified()->create([
        'created_at' => '2026-06-01 00:00:00',
    ]);

    // 15 days from creation = 2026-06-16, now = 2026-06-06 => 10 days left
    expect($user->daysLeftToVerify())->toBe(10);
});

it('clamps days left to verify at zero once expired', function () {
    Carbon::setTestNow('2026-07-01 00:00:00');

    $user = User::factory()->unverified()->create([
        'created_at' => '2026-06-01 00:00:00',
    ]);

    expect($user->daysLeftToVerify())->toBe(0);
});
