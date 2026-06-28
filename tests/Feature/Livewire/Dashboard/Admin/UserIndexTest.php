<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Jobs\ExportDataJob;
use App\Livewire\Dashboard\Admin\Users\UserIndex;
use App\Models\Module;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
    Queue::fake();
});

it('blocks non-admins from the users page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.users.index'))
        ->assertForbidden();
});

it('lets an admin open the users page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.users.index'))
        ->assertOk()
        ->assertSeeLivewire(UserIndex::class);
});

it('lists users but hides the logged-in admin', function () {
    $admin = User::factory()->superAdmin()->create(['name' => 'Eu Mesmo']);
    $other = User::factory()->writer()->create(['name' => 'Outro Usuário']);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('$refresh')
        ->assertSee('Outro Usuário')
        ->assertDontSee('Eu Mesmo');
});

it('prevents an admin from changing their own status', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('toggleStatus', $admin);

    expect($admin->fresh()->status)->toBe($admin->status);
});

it('toggles the verified badge of a user', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->writer()->create();
    Profile::factory()->create(['user_id' => $target->id, 'is_verified' => false]);

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('toggleVerification', $target);

    expect($target->fresh()->profile->is_verified)->toBeTrue();
});

it('confirms and deletes a user', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->writer()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('confirmUserDeletion', $target->id)
        ->assertSet('userIdBeingDeleted', $target->id)
        ->assertDispatched('open-modal', name: 'confirm-user-deletion')
        ->call('delete')
        ->assertSet('userIdBeingDeleted', null)
        ->assertDispatched('close-modal', name: 'confirm-user-deletion');

    expect(User::query()->whereKey($target->id)->exists())->toBeFalse();
});

it('opens the manage modules modal for a user', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->writer()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('manageModules', $target)
        ->assertSet('selectedUserForModules.id', $target->id)
        ->assertDispatched('open-modal', name: 'user-modules-modal');
});

it('toggles a module permission for the selected user', function () {
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->writer()->create();
    $module = Module::query()->first();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('manageModules', $target)
        ->call('toggleUserModule', $module->id);

    expect($target->fresh()->modules()->where('modules.id', $module->id)->exists())->toBeTrue();
});

it('queues a users export job', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('export');

    Queue::assertPushed(ExportDataJob::class);
});
