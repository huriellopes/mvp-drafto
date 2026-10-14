<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard;

use App\Livewire\Dashboard\NotificationBell;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

function makeNotification(User $user, array $data = [], ?string $readAt = null): void
{
    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\SocialInteractionNotification',
        'data' => array_merge(['link' => '/dashboard', 'message' => 'Olá'], $data),
        'read_at' => $readAt,
    ]);
}

it('counts only unread notifications', function () {
    $user = User::factory()->create();
    makeNotification($user);
    makeNotification($user);
    makeNotification($user, [], now()->toDateTimeString()); // already read

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSet('unreadCount', 2);
});

it('reports zero when there are no unread notifications', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertSet('unreadCount', 0);
});

it('re-renders when the notification-updated event is received', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->call('refresh')
        ->assertOk();
});

it('subscribes to the unread-changed event on the user private channel', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)->test(NotificationBell::class)->instance();
    $listeners = (fn () => $this->getListeners())->call($component);

    expect($listeners)->toBe([
        "echo-private:App.Models.User.{$user->id},.notifications.unread-changed" => 'refresh',
    ]);
});

it('no longer polls the server on a fixed interval', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(NotificationBell::class)
        ->assertDontSeeHtml('wire:poll')
        ->assertSeeHtml('realtimeFallback(60000)');
});
