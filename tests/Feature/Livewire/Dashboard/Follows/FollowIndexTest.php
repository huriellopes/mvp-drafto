<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Follows;

use App\Livewire\Dashboard\Follows\FollowIndex;
use App\Models\User;
use Livewire\Livewire;

it('renders the followers tab for an authorized user', function () {
    $user = User::factory()->writer()->create();
    $follower = User::factory()->withProfile()->create();
    $follower->following()->attach($user->id);

    Livewire::actingAs($user)
        ->test(FollowIndex::class)
        ->assertOk()
        ->assertSet('tab', 'followers');
});

it('switches tab and resets pagination', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(FollowIndex::class)
        ->set('tab', 'following')
        ->assertSet('tab', 'following');
});

it('toggles sort column and direction', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(FollowIndex::class)
        ->call('sortBy', 'name')
        ->assertSet('sort', 'name')
        ->assertSet('direction', 'asc')
        ->call('sortBy', 'name')
        ->assertSet('direction', 'desc');
});

it('confirms and unfollows a user', function () {
    $user = User::factory()->writer()->create();
    $target = User::factory()->create(['name' => 'Alvo']);
    $user->following()->attach($target->id);

    Livewire::actingAs($user)
        ->test(FollowIndex::class)
        ->call('confirmUnfollow', $target->id)
        ->assertSet('userIdToUnfollow', $target->id)
        ->assertDispatched('open-modal', name: 'confirm-unfollow-modal')
        ->call('unfollow')
        ->assertSet('userIdToUnfollow', null);

    expect($user->fresh()->isFollowing($target))->toBeFalse();
});

it('returns early from unfollow when nothing is selected', function () {
    $user = User::factory()->writer()->create();

    Livewire::actingAs($user)
        ->test(FollowIndex::class)
        ->call('unfollow')
        ->assertOk();
});
