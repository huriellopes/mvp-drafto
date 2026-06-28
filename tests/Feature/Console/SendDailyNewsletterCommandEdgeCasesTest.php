<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Jobs\SendNewsletterJob;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Support\Facades\Queue;

it('sends category-matched posts to a subscriber with interest categories', function () {
    Queue::fake();

    $category = PostCategory::factory()->create();
    Post::factory()->published()->create([
        'category_id' => $category->id,
        'created_at' => now(),
    ]);

    $subscriber = NewsletterSubscriber::factory()->verified()->create();
    $subscriber->categories()->attach($category->id);

    $this->artisan('drafto:send-newsletter')->assertExitCode(0);

    Queue::assertPushed(SendNewsletterJob::class, 1);
});

it('does not dispatch a job to a subscriber whose categories have no recent posts', function () {
    Queue::fake();

    // Recent post in a category the subscriber does NOT follow.
    Post::factory()->published()->create([
        'category_id' => PostCategory::factory()->create()->id,
        'created_at' => now(),
    ]);

    $otherCategory = PostCategory::factory()->create();
    $subscriber = NewsletterSubscriber::factory()->verified()->create();
    $subscriber->categories()->attach($otherCategory->id);

    $this->artisan('drafto:send-newsletter')->assertExitCode(0);

    Queue::assertNothingPushed();
});
