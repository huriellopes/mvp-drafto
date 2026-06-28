<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Enums\ModuleEnum;
use App\Enums\UserStatusEnum;
use App\Livewire\Dashboard\Admin\Users\UserIndex;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

/**
 * Covers the inactive-user branch of sendReengagement() (line 117-118).
 */
it('warns when re-engaging a user whose account is not active', function () {
    $target = User::factory()->writer()->create([
        'status' => UserStatusEnum::SUSPENDED,
        'wants_reengagement_emails' => true,
    ]);

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('confirmReengagement', $target)
        ->call('sendReengagement')
        ->assertSet('selectedUserForReengagement', null);
});

/**
 * Covers the banned-until branch of sendReengagement() (line 119-120).
 */
it('warns when re-engaging a user with suspended access', function () {
    $target = User::factory()->writer()->active()->create([
        'wants_reengagement_emails' => true,
        'banned_until' => now()->addWeek(),
    ]);

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('confirmReengagement', $target)
        ->call('sendReengagement')
        ->assertSet('selectedUserForReengagement', null);
});

/**
 * Covers the early return of impersonate() with no selected user (line 133-134).
 */
it('returns silently from impersonate without a selected user', function () {
    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('impersonate')
        ->assertOk();
});

/**
 * Covers the impersonation failure branch (line 140-141): impersonating
 * yourself makes the action return false.
 */
it('shows an error when impersonation fails', function () {
    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('confirmImpersonation', $this->admin)
        ->call('impersonate')
        ->assertNoRedirect()
        ->assertSet('selectedUserForImpersonation', null);
});

/**
 * Covers the early return of toggleUserModule() with no selected user (line 155-156).
 */
it('returns silently from toggleUserModule without a selected user', function () {
    $module = Module::first();

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('toggleUserModule', $module->id)
        ->assertOk();
});

/**
 * Covers the missing-module branch of toggleUserModule() (line 161-162).
 */
it('returns silently when the module to toggle does not exist', function () {
    $target = User::factory()->writer()->create();

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('manageModules', $target)
        ->call('toggleUserModule', 999999)
        ->assertOk();
});

/**
 * Covers the happy path of toggleUserModule() so the toaster line runs too.
 */
it('toggles an existing module for the selected user', function () {
    $target = User::factory()->writer()->create();
    $module = Module::where('slug', ModuleEnum::COMMENTS->value)->firstOrFail();

    Livewire::actingAs($this->admin)
        ->test(UserIndex::class)
        ->call('manageModules', $target)
        ->assertSet('selectedUserForModules.id', $target->id)
        ->call('toggleUserModule', $module->id)
        ->assertOk();
});
