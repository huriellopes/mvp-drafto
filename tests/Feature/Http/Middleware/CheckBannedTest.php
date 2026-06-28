<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;

it('logs out and redirects banned users to login with an error', function () {
    $user = User::factory()->create([
        'banned_until' => now()->addDays(5),
        'ban_reason' => 'Spam',
    ]);

    // CheckBanned is appended to the web middleware group, so any
    // authenticated web route triggers it.
    $response = $this->actingAs($user)->get(route('dashboard.index'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('allows users whose ban has already expired', function () {
    $user = User::factory()->create([
        'banned_until' => now()->subDay(),
        'ban_reason' => 'Old',
    ]);

    $this->actingAs($user)->get(route('dashboard.index'))
        ->assertOk();
});

it('allows users that were never banned', function () {
    $user = User::factory()->create(['banned_until' => null]);

    $this->actingAs($user)->get(route('dashboard.index'))
        ->assertOk();
});
