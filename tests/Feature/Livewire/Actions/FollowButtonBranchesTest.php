<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Actions;

use App\Livewire\Actions\FollowButton;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

it('redirects a guest to login when toggling follow', function () {
    $target = User::factory()->create();

    Livewire::test(FollowButton::class, ['user' => $target])
        ->call('toggle')
        ->assertRedirect(route('login'));
});

it('does nothing when a user tries to follow themselves', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(FollowButton::class, ['user' => $user])
        ->call('toggle')
        ->assertSet('isFollowing', false);

    expect($user->following()->count())->toBe(0);
});

it('follows and unfollows another user, dispatching follow-updated', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(FollowButton::class, ['user' => $target])
        ->assertSet('isFollowing', false)
        ->call('toggle')
        ->assertSet('isFollowing', true)
        ->assertDispatched('follow-updated');

    expect($user->fresh()->isFollowing($target))->toBeTrue();

    $component->call('toggle')
        ->assertSet('isFollowing', false)
        ->assertDispatched('follow-updated');

    expect($user->fresh()->isFollowing($target))->toBeFalse();
});

it('reflects the preloaded follow status on mount', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();
    $user->following()->attach($target->id);

    Livewire::actingAs($user)
        ->test(FollowButton::class, ['user' => $target])
        ->assertSet('isFollowing', true);
});
