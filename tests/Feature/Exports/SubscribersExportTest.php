<?php

declare(strict_types=1);

namespace Tests\Feature\Exports;

use App\DTOs\NewsletterFilterData;
use App\Exports\SubscribersExport;
use App\Models\NewsletterSubscriber;
use App\Models\PostCategory;

it('exposes the expected headings', function () {
    $export = new SubscribersExport(new NewsletterFilterData());

    expect($export->headings())->toBe(['ID', 'E-mail', 'Categorias de Interesse', 'Data de Inscrição']);
});

it('builds a query returning seeded rows', function () {
    NewsletterSubscriber::factory()->count(2)->create();

    expect((new SubscribersExport(new NewsletterFilterData()))->query()->count())->toBe(2);
});

it('filters by email search', function () {
    NewsletterSubscriber::factory()->create(['email' => 'findme@example.com']);
    NewsletterSubscriber::factory()->create(['email' => 'other@example.com']);

    expect((new SubscribersExport(new NewsletterFilterData(search: 'findme')))->query()->count())->toBe(1);
});

it('filters by category', function () {
    $category = PostCategory::factory()->create();
    $withCat = NewsletterSubscriber::factory()->create();
    $withCat->categories()->attach($category);
    NewsletterSubscriber::factory()->create();

    $export = new SubscribersExport(new NewsletterFilterData(category_id: $category->id));

    expect($export->query()->count())->toBe(1);
});

it('maps a subscriber with categories', function () {
    $category = PostCategory::factory()->create(['name' => 'Tech']);
    $subscriber = NewsletterSubscriber::factory()->create(['email' => 'sub@example.com']);
    $subscriber->categories()->attach($category);

    $export = new SubscribersExport(new NewsletterFilterData());
    $row = $export->map($export->query()->first());

    expect($row)->toHaveCount(4)
        ->and($row[1])->toBe('sub@example.com')
        ->and($row[2])->toBe('Tech');
});

it('falls back to "Geral" when the subscriber has no categories', function () {
    NewsletterSubscriber::factory()->create();

    $export = new SubscribersExport(new NewsletterFilterData());

    expect($export->map($export->query()->first())[2])->toBe('Geral');
});
