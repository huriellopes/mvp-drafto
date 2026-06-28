<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Jobs\SendNewsletterJob;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use Illuminate\Support\Facades\Queue;

it('dispatches newsletter jobs to verified subscribers for recent posts', function () {
    Queue::fake();

    Post::factory()->published()->create(['created_at' => now()]);

    NewsletterSubscriber::factory()->verified()->create();
    NewsletterSubscriber::factory()->create(); // não verificado, deve ser ignorado

    $this->artisan('drafto:send-newsletter')
        ->assertExitCode(0);

    Queue::assertPushed(SendNewsletterJob::class, 1);
});

it('skips the newsletter when there are no recent posts', function () {
    Queue::fake();

    NewsletterSubscriber::factory()->verified()->create();

    $this->artisan('drafto:send-newsletter')
        ->expectsOutputToContain('Nenhum post novo')
        ->assertExitCode(0);

    Queue::assertNothingPushed();
});
