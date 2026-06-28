<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard;

use App\Enums\RoleEnum;
use App\Livewire\Dashboard\ImpersonationBanner;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

it('leaves impersonation and redirects to the users admin page', function () {
    $admin = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $impersonated = User::factory()->create(['role' => RoleEnum::READER]);

    $this->actingAs($impersonated);
    Session::put('impersonator_id', $admin->id);

    Livewire::test(ImpersonationBanner::class)
        ->call('leave')
        ->assertRedirect(route('dashboard.admin.users.index'));

    expect(auth()->id())->toBe($admin->id)
        ->and(Session::has('impersonator_id'))->toBeFalse();
});

it('does not redirect when not impersonating', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ImpersonationBanner::class)
        ->call('leave')
        ->assertNoRedirect();
});
