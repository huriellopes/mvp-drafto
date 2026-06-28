<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessProfileViewJob;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

it('increments profile_views and sets the throttle key when not throttled', function (): void {
    $user = User::factory()->withProfile()->create();
    $profile = $user->profile;

    Redis::shouldReceive('get')->once()->andReturn(null);
    Redis::shouldReceive('setex')->once();

    app()->call([new ProcessProfileViewJob($profile->id, $user->id, 'ip-hash'), 'handle']);

    expect($profile->fresh()->profile_views)->toBe(1);
});

it('does not increment when already throttled', function (): void {
    $user = User::factory()->withProfile()->create();
    $profile = $user->profile;

    Redis::shouldReceive('get')->once()->andReturn('1');
    Redis::shouldReceive('setex')->never();

    app()->call([new ProcessProfileViewJob($profile->id, $user->id, 'ip-hash'), 'handle']);

    expect($profile->fresh()->profile_views)->toBe(0);
});

it('uses the ip hash in the throttle key for guests', function (): void {
    $user = User::factory()->withProfile()->create();
    $profile = $user->profile;

    Redis::shouldReceive('get')
        ->once()
        ->with("profile_view_throttle:{$profile->id}:guest-ip")
        ->andReturn(null);
    Redis::shouldReceive('setex')
        ->once()
        ->with("profile_view_throttle:{$profile->id}:guest-ip", 3600, '1');

    app()->call([new ProcessProfileViewJob($profile->id, null, 'guest-ip'), 'handle']);

    expect($profile->fresh()->profile_views)->toBe(1);
});
