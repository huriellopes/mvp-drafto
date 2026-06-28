<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Dashboard\Admin\Users\UserIndex;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

it('creates a user via the admin user form', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('openCreateModal')
        ->set('form.name', 'Novo Usuário')
        ->set('form.email', 'novo@example.com')
        ->set('form.password', 'secret123')
        ->set('form.role', 'reader')
        ->set('form.status', 'active')
        ->call('save')
        ->assertHasNoErrors();

    expect(User::query()->where('email', 'novo@example.com')->exists())->toBeTrue();
});

it('validates required fields when creating a user', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('openCreateModal')
        ->set('form.name', '')
        ->set('form.email', 'not-an-email')
        ->set('form.password', 'short')
        ->call('save')
        ->assertHasErrors([
            'form.name',
            'form.email',
            'form.password',
        ]);
});

it('rejects a duplicate email', function () {
    $admin = User::factory()->superAdmin()->create();
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('openCreateModal')
        ->set('form.name', 'Alguém')
        ->set('form.email', 'taken@example.com')
        ->set('form.password', 'secret123')
        ->set('form.role', 'reader')
        ->set('form.status', 'active')
        ->call('save')
        ->assertHasErrors(['form.email']);
});

it('makes password optional and ignores own email when editing an existing user', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->writer()->create(['name' => 'Antigo Nome']);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('edit', $target)
        ->assertSet('form.name', 'Antigo Nome')
        ->assertSet('form.email', $target->email)
        ->set('form.name', 'Nome Atualizado')
        ->set('form.password', '')
        ->call('save')
        ->assertHasNoErrors();

    expect($target->fresh()->name)->toBe('Nome Atualizado');
});

it('rejects an invalid role enum value', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('openCreateModal')
        ->set('form.name', 'Alguém')
        ->set('form.email', 'role@example.com')
        ->set('form.password', 'secret123')
        ->set('form.role', 'super-hacker')
        ->set('form.status', 'active')
        ->call('save')
        ->assertHasErrors(['form.role']);
});
