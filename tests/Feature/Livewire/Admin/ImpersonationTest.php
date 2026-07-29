<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Admin;

use App\Actions\Auth\ImpersonateUserAction;
use App\Actions\Auth\LeaveImpersonationAction;
use App\Enums\RoleEnum;
use App\Livewire\Dashboard\Admin\Users\UserIndex;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

it('allows a super admin to impersonate another user', function () {
    $admin = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $user = User::factory()->create(['role' => RoleEnum::READER]);

    $this->actingAs($admin);

    Livewire::test(UserIndex::class)
        ->call('confirmImpersonation', $user)
        ->call('impersonate')
        ->assertRedirect(route('dashboard.index'));

    expect(auth()->id())->toBe($user->id)
        ->and(Session::get('impersonator_id'))->toBe($admin->id);
});

it('does not allow a regular user to impersonate', function () {
    $user1 = User::factory()->create(['role' => RoleEnum::READER]);
    $user2 = User::factory()->create(['role' => RoleEnum::READER]);

    $this->actingAs($user1);

    $action = new ImpersonateUserAction;
    $result = $action->exec($user2);

    expect($result)->toBeFalse()
        ->and(auth()->id())->toBe($user1->id)
        ->and(Session::has('impersonator_id'))->toBeFalse();
});

it('allows leaving impersonation mode', function () {
    $admin = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $user = User::factory()->create(['role' => RoleEnum::READER]);

    // Simula estar em modo impersonação
    $this->actingAs($user);
    Session::put('impersonator_id', $admin->id);

    $action = new LeaveImpersonationAction;
    $result = $action->exec();

    expect($result)->toBeTrue()
        ->and(auth()->id())->toBe($admin->id)
        ->and(Session::has('impersonator_id'))->toBeFalse();
});
