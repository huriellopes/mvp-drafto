<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Enums\ModuleEnum;
use App\Enums\PostStatusEnum;
use App\Models\Module;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

it('publishes due scheduled posts when the module is enabled', function () {
    Notification::fake();

    $due = Post::factory()->create([
        'status' => PostStatusEnum::SCHEDULED,
        'published_at' => now()->subMinute(),
    ]);

    $future = Post::factory()->create([
        'status' => PostStatusEnum::SCHEDULED,
        'published_at' => now()->addDay(),
    ]);

    $this->artisan('posts:publish-scheduled')
        ->assertExitCode(0);

    expect($due->fresh()->status)->toBe(PostStatusEnum::PUBLISHED)
        ->and($future->fresh()->status)->toBe(PostStatusEnum::SCHEDULED);
});

it('does nothing when the post scheduler module is disabled', function () {
    Module::query()
        ->where('slug', ModuleEnum::POST_SCHEDULER->value)
        ->update(['is_enabled' => false]);
    Cache::flush();

    $due = Post::factory()->create([
        'status' => PostStatusEnum::SCHEDULED,
        'published_at' => now()->subMinute(),
    ]);

    $this->artisan('posts:publish-scheduled')
        ->expectsOutputToContain('módulo de agendamento')
        ->assertExitCode(0);

    expect($due->fresh()->status)->toBe(PostStatusEnum::SCHEDULED);
});
