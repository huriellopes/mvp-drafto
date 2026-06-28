<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Public;

use App\Actions\Public\ListPublicPostsAction;
use App\DTOs\Public\PostFilterData;
use App\Enums\PostVisibilityEnum;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->action = app(ListPublicPostsAction::class);
});

function makeFollowersOnlyPost(User $author): Post
{
    return Post::factory()->published()->create([
        'user_id' => $author->id,
        'visibility' => PostVisibilityEnum::FOLLOWERS_ONLY,
    ]);
}

it('hides followers-only posts from guests', function () {
    $author = User::factory()->writer()->withProfile()->create();
    makeFollowersOnlyPost($author);

    $result = $this->action->exec(PostFilterData::from([]));

    expect($result->total())->toBe(0);
});

it('shows followers-only posts to a follower', function () {
    $author = User::factory()->writer()->withProfile()->create();
    $follower = User::factory()->withProfile()->create();
    $author->followers()->attach($follower->id);

    $post = makeFollowersOnlyPost($author);

    $this->actingAs($follower);

    $result = $this->action->exec(PostFilterData::from([]));

    expect($result->pluck('id')->all())->toContain($post->id);
});

it('shows followers-only posts to the author themselves', function () {
    $author = User::factory()->writer()->withProfile()->create();
    $post = makeFollowersOnlyPost($author);

    $this->actingAs($author);

    $result = $this->action->exec(PostFilterData::from([]));

    expect($result->pluck('id')->all())->toContain($post->id);
});

it('hides followers-only posts from a non-follower authenticated user', function () {
    $author = User::factory()->writer()->withProfile()->create();
    $stranger = User::factory()->withProfile()->create();
    makeFollowersOnlyPost($author);

    $this->actingAs($stranger);

    $result = $this->action->exec(PostFilterData::from([]));

    expect($result->total())->toBe(0);
});

it('shows all followers-only posts to an admin', function () {
    $author = User::factory()->writer()->withProfile()->create();
    $admin = User::factory()->superAdmin()->withProfile()->create();
    $post = makeFollowersOnlyPost($author);

    $this->actingAs($admin);

    $result = $this->action->exec(PostFilterData::from([]));

    expect($result->pluck('id')->all())->toContain($post->id);
});

it('orders by views_count when sort is popular', function () {
    $low = Post::factory()->published()->public()->create(['views_count' => 1]);
    $high = Post::factory()->published()->public()->create(['views_count' => 999]);

    $result = $this->action->exec(PostFilterData::from(['sort' => 'popular']));

    expect($result->first()->id)->toBe($high->id)
        ->and($result->pluck('id')->all())->toContain($low->id);
});

it('orders by comments_count when sort is commented', function () {
    Post::factory()->published()->public()->create(['comments_count' => 0]);
    $high = Post::factory()->published()->public()->create(['comments_count' => 50]);

    $result = $this->action->exec(PostFilterData::from(['sort' => 'commented']));

    expect($result->first()->id)->toBe($high->id);
});
