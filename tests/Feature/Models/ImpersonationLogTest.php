<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

it('is fillable and casts the timestamps', function () {
    $impersonator = User::factory()->superAdmin()->create();
    $impersonated = User::factory()->create();

    $log = ImpersonationLog::create([
        'impersonator_id' => $impersonator->id,
        'impersonated_id' => $impersonated->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
        'started_at' => '2026-01-01 09:00:00',
        'ended_at' => '2026-01-01 10:00:00',
    ]);

    expect($log->started_at)->toBeInstanceOf(Carbon::class)
        ->and($log->ended_at)->toBeInstanceOf(Carbon::class)
        ->and($log->ip_address)->toBe('127.0.0.1');
});

it('relates to the impersonator and impersonated users', function () {
    $impersonator = User::factory()->superAdmin()->create();
    $impersonated = User::factory()->create();

    $log = ImpersonationLog::create([
        'impersonator_id' => $impersonator->id,
        'impersonated_id' => $impersonated->id,
        'started_at' => now(),
    ]);

    expect($log->impersonator())->toBeInstanceOf(BelongsTo::class)
        ->and($log->impersonator->id)->toBe($impersonator->id)
        ->and($log->impersonated())->toBeInstanceOf(BelongsTo::class)
        ->and($log->impersonated->id)->toBe($impersonated->id);
});
