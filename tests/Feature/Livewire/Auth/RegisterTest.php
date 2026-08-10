<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('renders the registration page', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSeeLivewire(Register::class);
});

it('can register a new user', function () {
    Notification::fake();

    $sessionIdBefore = session()->getId();

    Livewire::test(Register::class)
        ->set('form.name', 'New User')
        ->set('form.email', 'newuser@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->set('form.role', 'reader')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.index'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
        'name' => 'New User',
    ]);

    // Segurança: o ID de sessão deve ser regenerado no login pós-cadastro,
    // como proteção contra session fixation.
    expect(session()->getId())->not->toBe($sessionIdBefore);
});

it('validates registration required fields', function () {
    Livewire::test(Register::class)
        ->set('form.role', '')
        ->call('register')
        ->assertHasErrors(['form.name', 'form.email', 'form.password', 'form.role']);
});

it('prevents double registration with same email', function () {
    User::factory()->create(['email' => 'duplicate@example.com']);

    Livewire::test(Register::class)
        ->set('form.email', 'duplicate@example.com')
        ->call('register')
        ->assertHasErrors(['form.email']);
});
