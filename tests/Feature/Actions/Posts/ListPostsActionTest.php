<?php

declare(strict_types=1);

use App\Actions\Posts\ListPostsAction;
use App\DTOs\PostFiltersData;
use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->action = new ListPostsAction();
});

it('lists only the authenticated user posts', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Post::factory()->count(3)->forAuthor($user)->create();
    Post::factory()->count(2)->forAuthor($other)->create();

    $this->actingAs($user);

    $result = $this->action->exec(new PostFiltersData());

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(3);

    $result->getCollection()->each(function (Post $post) use ($user) {
        expect($post->user_id)->toBe($user->id);
    });
});

it('filters posts by search term on the title', function () {
    $user = User::factory()->create();

    Post::factory()->forAuthor($user)->create(['title' => 'Laravel Testing Guide']);
    Post::factory()->forAuthor($user)->create(['title' => 'Cooking Recipes']);

    $this->actingAs($user);

    $result = $this->action->exec(new PostFiltersData(search: 'Laravel'));

    expect($result->total())->toBe(1)
        ->and($result->first()->title)->toBe('Laravel Testing Guide');
});

it('filters posts by status', function () {
    $user = User::factory()->create();

    Post::factory()->count(2)->forAuthor($user)->draft()->create();
    Post::factory()->count(1)->forAuthor($user)->published()->create();

    $this->actingAs($user);

    $result = $this->action->exec(new PostFiltersData(status: PostStatusEnum::PUBLISHED));

    expect($result->total())->toBe(1)
        ->and($result->first()->status)->toBe(PostStatusEnum::PUBLISHED);
});

it('excludes posts matching the notStatus filter', function () {
    $user = User::factory()->create();

    Post::factory()->count(2)->forAuthor($user)->archived()->create();
    Post::factory()->count(3)->forAuthor($user)->draft()->create();

    $this->actingAs($user);

    $result = $this->action->exec(new PostFiltersData(notStatus: PostStatusEnum::ARCHIVED));

    expect($result->total())->toBe(3);

    $result->getCollection()->each(function (Post $post) {
        expect($post->status)->not->toBe(PostStatusEnum::ARCHIVED);
    });
});
