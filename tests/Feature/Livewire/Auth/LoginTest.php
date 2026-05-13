<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

it('renders the login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSeeLivewire(Login::class);
});

it('can authenticate a user', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.index'));

    $this->assertAuthenticatedAs($user);
});

it('cannot authenticate with invalid password', function () {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['form.email']);

    $this->assertGuest();
});

it('validates required fields', function () {
    Livewire::test(Login::class)
        ->call('login')
        ->assertHasErrors(['form.email', 'form.password']);
});
