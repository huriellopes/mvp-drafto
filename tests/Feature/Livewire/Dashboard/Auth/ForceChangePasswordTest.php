<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Auth;

use App\Livewire\Dashboard\Auth\ForceChangePassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('changes the password and clears the must_change_password flag', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    Livewire::actingAs($user)
        ->test(ForceChangePassword::class)
        ->set('password', 'NewStrongP@ss1')
        ->set('password_confirmation', 'NewStrongP@ss1')
        ->call('changePassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard.index'));

    $user->refresh();

    expect($user->must_change_password)->toBeFalse()
        ->and(Hash::check('NewStrongP@ss1', $user->password))->toBeTrue();
});

it('validates the new password against the strength rules', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    Livewire::actingAs($user)
        ->test(ForceChangePassword::class)
        ->set('password', 'weak')
        ->set('password_confirmation', 'weak')
        ->call('changePassword')
        ->assertHasErrors('password');

    expect($user->fresh()->must_change_password)->toBeTrue();
});

it('requires the password confirmation to match', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    Livewire::actingAs($user)
        ->test(ForceChangePassword::class)
        ->set('password', 'NewStrongP@ss1')
        ->set('password_confirmation', 'Different@1')
        ->call('changePassword')
        ->assertHasErrors('password');
});
