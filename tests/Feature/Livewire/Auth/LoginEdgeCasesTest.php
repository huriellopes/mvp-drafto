<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

/**
 * Covers the authenticated-user redirect in mount() (lines 22-23): an already
 * logged-in user visiting the login component is sent to the dashboard.
 */
it('redirects an already authenticated user away from login', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Login::class)
        ->assertRedirect(route('dashboard.index'));
});
