<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Follows;

use App\Actions\Follows\ListFollowsAction;
use App\DTOs\FollowersFilterData;
use App\Models\User;

beforeEach(function () {
    $this->action = app(ListFollowsAction::class);
});

it('lists the followers of a user', function () {
    $user = User::factory()->withProfile()->create();
    $followerA = User::factory()->withProfile()->create();
    $followerB = User::factory()->withProfile()->create();

    $user->followers()->attach([$followerA->id, $followerB->id]);

    $result = $this->action->exec($user, new FollowersFilterData, type: 'followers');

    expect($result->total())->toBe(2)
        ->and($result->pluck('id')->all())->toContain($followerA->id, $followerB->id);
});

it('lists who the user is following', function () {
    $user = User::factory()->withProfile()->create();
    $followed = User::factory()->withProfile()->create();

    $user->following()->attach($followed->id);

    $result = $this->action->exec($user, new FollowersFilterData, type: 'following');

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($followed->id);
});

it('filters followers by name', function () {
    $user = User::factory()->withProfile()->create();
    $match = User::factory()->withProfile()->create(['name' => 'Searchable Person']);
    $other = User::factory()->withProfile()->create();

    $user->followers()->attach([$match->id, $other->id]);

    $result = $this->action->exec($user, new FollowersFilterData(search: 'Searchable Person'));

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($match->id);
});
