<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Settings;

use App\Livewire\Dashboard\Settings\AccountSettings;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

it('renders the account settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertSeeLivewire(AccountSettings::class);
});

it('can update account information', function () {
    Notification::fake();

    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->set('form.name', 'Updated Name')
        ->set('form.email', 'updated@example.com')
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.com');
});

it('can update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->set('form.password', 'new-password-123')
        ->set('form.password_confirmation', 'new-password-123')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('new-password-123', $user->refresh()->password))->toBeTrue();
});

it('validates password confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->set('form.password', 'new-password')
        ->set('form.password_confirmation', 'wrong-confirmation')
        ->call('save')
        ->assertHasErrors(['form.password']);
});

it('can export the own data as a json download', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->call('exportData')
        ->assertFileDownloaded('drafto-meus-dados-' . now()->format('Y-m-d') . '.json');
});

it('can delete the own account when the password is confirmed', function () {
    $user = User::factory()->create(['password' => Hash::make('senha-correta')]);

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->set('deletePassword', 'senha-correta')
        ->call('deleteAccount')
        ->assertHasNoErrors()
        ->assertRedirect(route('home'));

    expect(auth()->check())->toBeFalse()
        ->and(User::query()->whereKey($user->id)->exists())->toBeFalse();

    $this->assertDatabaseHas('deleted_models', [
        'key' => $user->id,
        'model' => $user->getMorphClass(),
    ]);
});

it('does not delete the account when the password is wrong', function () {
    $user = User::factory()->create(['password' => Hash::make('senha-correta')]);

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->set('deletePassword', 'senha-errada')
        ->call('deleteAccount')
        ->assertHasErrors(['deletePassword' => 'current_password']);

    expect(auth()->check())->toBeTrue()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue();
});

it('requires the password to delete the account', function () {
    $user = User::factory()->create(['password' => Hash::make('senha-correta')]);

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->call('deleteAccount')
        ->assertHasErrors(['deletePassword' => 'required']);

    expect(User::query()->whereKey($user->id)->exists())->toBeTrue();
});
