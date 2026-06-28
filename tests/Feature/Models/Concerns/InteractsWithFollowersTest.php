<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use App\Models\User;

it('reports following status correctly', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();
    $stranger = User::factory()->create();

    $follower->following()->attach($followed->id);

    expect($follower->isFollowing($followed))->toBeTrue()
        ->and($follower->isFollowing($stranger))->toBeFalse();
});

it('exposes the followers relationship inversely', function () {
    $follower = User::factory()->create();
    $followed = User::factory()->create();

    $follower->following()->attach($followed->id);

    expect($followed->followers)->toHaveCount(1)
        ->and($followed->followers->first()->id)->toBe($follower->id);
});

it('adds the follow-status flag for the authenticated user', function () {
    $auth = User::factory()->create();
    $target = User::factory()->create();
    $other = User::factory()->create();

    $auth->following()->attach($target->id);

    $this->actingAs($auth);

    $followed = User::query()->withFollowStatus()->find($target->id);
    $notFollowed = User::query()->withFollowStatus()->find($other->id);

    expect((bool) $followed->is_followed_by_auth_user)->toBeTrue()
        ->and((bool) $notFollowed->is_followed_by_auth_user)->toBeFalse();
});

it('skips the follow-status flag when not authenticated', function () {
    $target = User::factory()->create();

    $result = User::query()->withFollowStatus()->find($target->id);

    expect($result->getAttribute('is_followed_by_auth_user'))->toBeNull();
});
