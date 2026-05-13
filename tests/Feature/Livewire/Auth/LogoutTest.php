<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\Logout;
use App\Models\User;
use Livewire\Livewire;

it('can logout an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Logout::class)
        ->call('logout')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
