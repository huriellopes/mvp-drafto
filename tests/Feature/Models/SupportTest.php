<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\SupportStatusEnum;
use App\Models\Support;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

it('casts status and responded_at', function () {
    $user = User::factory()->create();

    $support = Support::create([
        'user_id' => $user->id,
        'subject' => 'Help',
        'message' => 'I need help',
        'status' => SupportStatusEnum::RESOLVED->value,
        'responded_at' => '2026-01-01 12:00:00',
    ]);

    expect($support->status)->toBeInstanceOf(SupportStatusEnum::class)
        ->and($support->status)->toBe(SupportStatusEnum::RESOLVED)
        ->and($support->responded_at)->toBeInstanceOf(Carbon::class);
});

it('relates to the user and responder', function () {
    $user = User::factory()->create();
    $responder = User::factory()->superAdmin()->create();

    $support = Support::create([
        'user_id' => $user->id,
        'subject' => 'Help',
        'message' => 'I need help',
        'status' => SupportStatusEnum::PENDING->value,
        'responded_by' => $responder->id,
    ]);

    expect($support->user())->toBeInstanceOf(BelongsTo::class)
        ->and($support->user->id)->toBe($user->id)
        ->and($support->responder())->toBeInstanceOf(BelongsTo::class)
        ->and($support->responder->id)->toBe($responder->id);
});
