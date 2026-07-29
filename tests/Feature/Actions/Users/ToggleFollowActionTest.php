<?php

declare(strict_types=1);

use App\Actions\Users\ToggleFollowAction;
use App\Models\User;
use App\Notifications\SocialInteractionNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->action = new ToggleFollowAction;
});

it('attaches a follow and notifies the target', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create();

    Notification::fake();

    $isAttached = $this->action->exec($follower, $target);

    expect($isAttached)->toBeTrue()
        ->and($follower->following()->where('users.id', $target->id)->exists())->toBeTrue();

    Notification::assertSentTo(
        [$target],
        SocialInteractionNotification::class,
        function ($notification) use ($follower) {
            return $notification->type === 'follow'
                && $notification->causer->id === $follower->id;
        },
    );
});

it('detaches a follow and sends no notification', function () {
    $follower = User::factory()->create();
    $target = User::factory()->create();
    $follower->following()->attach($target->id);

    Notification::fake();

    $isAttached = $this->action->exec($follower, $target);

    expect($isAttached)->toBeFalse()
        ->and($follower->following()->where('users.id', $target->id)->exists())->toBeFalse();

    Notification::assertNothingSent();
});

it('does not allow a user to follow themselves', function () {
    $user = User::factory()->create();

    Notification::fake();

    $isAttached = $this->action->exec($user, $user);

    expect($isAttached)->toBeFalse()
        ->and($user->following()->where('users.id', $user->id)->exists())->toBeFalse();

    Notification::assertNothingSent();
});
