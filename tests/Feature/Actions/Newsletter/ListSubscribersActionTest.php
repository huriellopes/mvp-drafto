<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Newsletter;

use App\Actions\Newsletter\ListSubscribersAction;
use App\DTOs\NewsletterFilterData;
use App\Models\NewsletterSubscriber;
use App\Models\PostCategory;

beforeEach(function () {
    $this->action = app(ListSubscribersAction::class);
});

it('paginates subscribers with categories eager loaded', function () {
    NewsletterSubscriber::factory()->count(3)->create();

    $result = $this->action->exec(new NewsletterFilterData);

    expect($result->total())->toBe(3)
        ->and($result->first()->relationLoaded('categories'))->toBeTrue();
});

it('filters subscribers by email', function () {
    NewsletterSubscriber::factory()->create(['email' => 'target@example.com']);
    NewsletterSubscriber::factory()->count(2)->create();

    $result = $this->action->exec(new NewsletterFilterData(search: 'target@example.com'));

    expect($result->total())->toBe(1);
});

it('filters subscribers by category', function () {
    $category = PostCategory::factory()->create();
    $subscriber = NewsletterSubscriber::factory()->create();
    $subscriber->categories()->attach($category->id);
    NewsletterSubscriber::factory()->count(2)->create();

    $result = $this->action->exec(new NewsletterFilterData(category_id: $category->id));

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($subscriber->id);
});
