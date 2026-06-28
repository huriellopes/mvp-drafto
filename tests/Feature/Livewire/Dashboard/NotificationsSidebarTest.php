<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard;

use App\Livewire\Dashboard\NotificationsSidebar;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

function createNotification(User $user, array $data = [], ?string $readAt = null): string
{
    $id = (string) Str::uuid();

    $user->notifications()->create([
        'id' => $id,
        'type' => 'App\\Notifications\\SocialInteractionNotification',
        'data' => array_merge(['link' => '/dashboard/posts', 'message' => 'Algo aconteceu'], $data),
        'read_at' => $readAt,
    ]);

    return $id;
}

it('toggles visibility and dispatches notification-updated when opening', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->assertSet('show', false)
        ->call('toggle')
        ->assertSet('show', true)
        ->assertDispatched('notification-updated')
        ->call('toggle')
        ->assertSet('show', false);
});

it('increases the amount when loading more', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->assertSet('amount', 20)
        ->call('loadMore')
        ->assertSet('amount', 40);
});

it('marks a notification as read and redirects to its link', function () {
    $user = User::factory()->create();
    $id = createNotification($user, ['link' => '/dashboard/posts']);

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->call('readAndRedirect', $id)
        ->assertDispatched('notification-updated')
        ->assertRedirect('/dashboard/posts');

    expect($user->unreadNotifications()->count())->toBe(0);
});

it('does nothing when redirecting an unknown notification', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->call('readAndRedirect', (string) Str::uuid())
        ->assertNoRedirect();
});

it('marks a single notification as read', function () {
    $user = User::factory()->create();
    $id = createNotification($user);

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->call('markAsRead', $id)
        ->assertDispatched('notification-updated');

    expect($user->unreadNotifications()->count())->toBe(0);
});

it('deletes a single notification', function () {
    $user = User::factory()->create();
    $id = createNotification($user);

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->call('delete', $id)
        ->assertDispatched('notification-updated');

    expect($user->notifications()->count())->toBe(0);
});

it('marks all notifications as read', function () {
    $user = User::factory()->create();
    createNotification($user);
    createNotification($user);

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->call('markAllAsRead')
        ->assertDispatched('notification-updated');

    expect($user->unreadNotifications()->count())->toBe(0);
});

it('deletes all notifications', function () {
    $user = User::factory()->create();
    createNotification($user);
    createNotification($user);

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->call('deleteAll')
        ->assertDispatched('notification-updated');

    expect($user->notifications()->count())->toBe(0);
});

it('lists the latest notifications', function () {
    $user = User::factory()->create();
    createNotification($user, ['message' => 'Mensagem visível']);

    Livewire::actingAs($user)
        ->test(NotificationsSidebar::class)
        ->assertCount('notifications', 1);
});
