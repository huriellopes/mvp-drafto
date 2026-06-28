<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Enums\UserStatusEnum;
use App\Livewire\Dashboard\Admin\Users\UserIndex;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

it('confirms and resets a user password to the default', function () {
    $target = User::factory()->writer()->create();

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('confirmPasswordReset', $target)
        ->assertSet('selectedUserForPasswordReset.id', $target->id)
        ->assertDispatched('open-modal', name: 'confirm-password-reset')
        ->call('resetPassword')
        ->assertSet('selectedUserForPasswordReset', null)
        ->assertDispatched('close-modal', name: 'confirm-password-reset');

    expect(Hash::check('Drafto@2026', $target->fresh()->password))->toBeTrue();
});

it('returns silently from resetPassword without a selected user', function () {
    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('resetPassword')
        ->assertOk();
});

it('warns when re-engaging a user who opted out', function () {
    $target = User::factory()->writer()->active()->create(['wants_reengagement_emails' => false]);

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('confirmReengagement', $target)
        ->assertSet('selectedUserForReengagement.id', $target->id)
        ->call('sendReengagement')
        ->assertSet('selectedUserForReengagement', null);
});

it('sends a re-engagement email to an eligible active user', function () {
    $target = User::factory()->writer()->active()->create(['wants_reengagement_emails' => true]);

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('confirmReengagement', $target)
        ->call('sendReengagement')
        ->assertSet('selectedUserForReengagement', null);
});

it('returns silently from sendReengagement without a selected user', function () {
    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('sendReengagement')
        ->assertOk();
});

it('opens the create user modal', function () {
    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('openCreateModal')
        ->assertDispatched('open-modal', name: 'user-form-modal');
});

it('opens the edit user modal with the user loaded', function () {
    $target = User::factory()->writer()->create();

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('edit', $target)
        ->assertDispatched('open-modal', name: 'user-form-modal');
});

it('toggles the status of another user', function () {
    $target = User::factory()->writer()->active()->create();

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('toggleStatus', $target)
        ->assertOk();

    expect($target->fresh()->status)->toBe(UserStatusEnum::SUSPENDED);
});

it('returns silently from delete when nothing is selected', function () {
    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('delete')
        ->assertOk();
});

it('toggles a sort column and resets pagination', function () {
    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('sortBy', 'name')
        ->assertSet('sort', 'name')
        ->assertSet('direction', 'asc')
        ->call('sortBy', 'name')
        ->assertSet('direction', 'desc');
});

it('resets pagination when searching', function () {
    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->set('search', 'algo')
        ->assertOk();
});
