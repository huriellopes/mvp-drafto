<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Newsletter;

use App\Actions\Newsletter\SendBroadcastAction;
use App\Jobs\SendNewsletterJob;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->action = app(SendBroadcastAction::class);
});

it('dispatches a job for every verified, opted-in subscriber', function () {
    NewsletterSubscriber::factory()->verified()->count(2)->create(['receive_platform_updates' => true]);

    $this->action->exec('Hello everyone', 'Big news');

    Queue::assertPushed(SendNewsletterJob::class, 2);
});

it('skips unverified subscribers', function () {
    NewsletterSubscriber::factory()->create(['verified_at' => null, 'receive_platform_updates' => true]);

    $this->action->exec('Hello');

    Queue::assertNothingPushed();
});

it('skips subscribers who opted out of platform updates', function () {
    NewsletterSubscriber::factory()->verified()->create(['receive_platform_updates' => false]);

    $this->action->exec('Hello');

    Queue::assertNothingPushed();
});
