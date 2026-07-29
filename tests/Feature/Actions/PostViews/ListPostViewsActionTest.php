<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\PostViews;

use App\Actions\PostViews\ListPostViewsAction;
use App\DTOs\PostViewFilterData;
use App\Models\Post;
use App\Models\PostView;

beforeEach(function () {
    $this->action = app(ListPostViewsAction::class);
});

it('paginates post views with eager loaded relations', function () {
    PostView::factory()->count(3)->create();

    $result = $this->action->exec(new PostViewFilterData);

    expect($result->total())->toBe(3)
        ->and($result->first()->relationLoaded('post'))->toBeTrue();
});

it('filters by post title', function () {
    $post = Post::factory()->published()->create(['title' => 'Unique Searchable Title']);
    PostView::factory()->forPost($post)->create();
    PostView::factory()->count(2)->create();

    $result = $this->action->exec(new PostViewFilterData(search: 'Unique Searchable'));

    expect($result->total())->toBe(1)
        ->and($result->first()->post_id)->toBe($post->id);
});

it('filters by ip hash', function () {
    PostView::factory()->create(['ip_hash' => '203.0.113.42']);
    PostView::factory()->count(2)->create(['ip_hash' => '10.0.0.1']);

    $result = $this->action->exec(new PostViewFilterData(search: '203.0.113.42'));

    expect($result->total())->toBe(1);
});

it('honors the per page argument', function () {
    PostView::factory()->count(5)->create();

    $result = $this->action->exec(new PostViewFilterData, perPage: 2);

    expect($result->perPage())->toBe(2)
        ->and($result->items())->toHaveCount(2);
});
