<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Actions\Admin\GenerateDailySummaryAction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

it('counts new users from the last 24h and excludes older ones', function () {
    User::factory()->count(3)->create();
    User::factory()->create(['created_at' => Carbon::now()->subDays(2)]);

    expect(app(GenerateDailySummaryAction::class)->exec()->newUsers)->toBe(3);
});

it('counts only posts published within the last 24h', function () {
    $writer = User::factory()->writer()->create();

    Post::factory()->published()->for($writer)->create(['published_at' => Carbon::now()->subHours(2)]);
    Post::factory()->published()->for($writer)->create(['published_at' => Carbon::now()->subDays(2)]);

    expect(app(GenerateDailySummaryAction::class)->exec()->newPosts)->toBe(1);
});

it('reports zero failed jobs when there are none', function () {
    expect(app(GenerateDailySummaryAction::class)->exec()->failedJobs)->toBe(0);
});

it('sends the daily summary to telegram and succeeds', function () {
    config(['services.telegram.token' => 'TEST_TOKEN', 'services.telegram.chat' => '999']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

    User::factory()->count(2)->create();

    $this->artisan('drafto:daily-summary')->assertExitCode(0);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'api.telegram.org')
        && str_contains($request['text'] ?? '', 'Resumo diário'));
});
