<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Actions\Auth\GenerateTwoFactorSecretAction;
use App\Livewire\Auth\TwoFactorChallenge;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

it('redirects to login when there is no pending 2fa session', function () {
    Livewire::test(TwoFactorChallenge::class)
        ->assertRedirect(route('login'));
});

it('shows an error for an invalid 2fa code', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    session(['auth.2fa.id' => $user->id]);

    Livewire::test(TwoFactorChallenge::class)
        ->set('code', '000000')
        ->call('verify')
        ->assertHasErrors('code');

    expect(Auth::check())->toBeFalse();
});

it('toggles between code and recovery modes resetting the code', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    session(['auth.2fa.id' => $user->id]);

    Livewire::test(TwoFactorChallenge::class)
        ->set('code', '123456')
        ->assertSet('recovery', false)
        ->call('toggleRecovery')
        ->assertSet('recovery', true)
        ->assertSet('code', '')
        ->call('toggleRecovery')
        ->assertSet('recovery', false);
});
