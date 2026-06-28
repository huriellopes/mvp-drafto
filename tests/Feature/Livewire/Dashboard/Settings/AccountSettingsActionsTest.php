<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Settings;

use App\Enums\RoleEnum;
use App\Livewire\Dashboard\Settings\AccountSettings;
use App\Models\User;
use Livewire\Livewire;

it('opens the become writer modal', function () {
    $user = User::factory()->reader()->create();

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->call('openBecomeWriterModal')
        ->assertDispatched('open-modal', name: 'confirm-become-writer');
});

it('opens the delete account modal', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->call('openDeleteAccountModal')
        ->assertDispatched('open-modal', name: 'confirm-delete-account');
});

it('upgrades a reader to a writer', function () {
    $user = User::factory()->reader()->create();

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->call('becomeWriter')
        ->assertDispatched('close-modal', name: 'confirm-become-writer')
        ->assertRedirect(route('dashboard.account'));

    expect($user->fresh()->role)->toBe(RoleEnum::WRITER);
});

it('does not change role when user is not a reader', function () {
    $user = User::factory()->writer()->create();

    $this->actingAs($user);

    Livewire::test(AccountSettings::class)
        ->call('becomeWriter');

    expect($user->fresh()->role)->toBe(RoleEnum::WRITER);
});
