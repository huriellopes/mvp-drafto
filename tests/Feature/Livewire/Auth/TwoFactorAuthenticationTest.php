<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Actions\Auth\GenerateTwoFactorSecretAction;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\TwoFactorChallenge;
use App\Livewire\Dashboard\Settings\TwoFactorManager;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use PragmaRX\Google2FALaravel\Google2FA;

it('can enable and confirm 2fa', function () {
    $user = User::factory()->create();
    $google2fa = app(Google2FA::class);

    Livewire::actingAs($user)
        ->test(TwoFactorManager::class)
        ->call('enable')
        ->assertSet('showingQrCode', true)
        ->assertSet('showingConfirmation', true);

    $user->refresh();
    $secret = $user->two_factor_secret;
    $code = $google2fa->getCurrentOtp($secret);

    Livewire::actingAs($user)
        ->test(TwoFactorManager::class)
        ->set('code', $code)
        ->call('confirm')
        ->assertSet('showingConfirmation', false)
        ->assertSet('showingRecoveryCodes', true);

    expect($user->refresh()->hasTwoFactorEnabled())->toBeTrue();
});

it('redirects to 2fa challenge when enabled', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertRedirect(route('auth.two-factor'));

    expect(Auth::check())->toBeFalse();
    expect(session()->has('auth.2fa.id'))->toBeTrue();
});

it('can login with valid 2fa code', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    session(['auth.2fa.id' => $user->id]);

    $google2fa = app(Google2FA::class);
    $code = $google2fa->getCurrentOtp($user->two_factor_secret);

    Livewire::test(TwoFactorChallenge::class)
        ->set('code', $code)
        ->call('verify')
        ->assertRedirect(route('dashboard.index'));

    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

it('can login with recovery code', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    $recoveryCode = $user->two_factor_recovery_codes[0];

    session(['auth.2fa.id' => $user->id]);

    Livewire::test(TwoFactorChallenge::class)
        ->set('recovery', true)
        ->set('code', $recoveryCode)
        ->call('verify')
        ->assertRedirect(route('dashboard.index'));

    expect(Auth::check())->toBeTrue();
    expect(count($user->refresh()->two_factor_recovery_codes))->toBe(7);
});

it('can disable 2fa with the correct current password', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    Livewire::actingAs($user)
        ->test(TwoFactorManager::class)
        ->set('currentPassword', 'password')
        ->call('disable');

    expect($user->refresh()->hasTwoFactorEnabled())->toBeFalse();
    expect($user->two_factor_secret)->toBeNull();
});

it('cannot disable 2fa with an incorrect current password', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    Livewire::actingAs($user)
        ->test(TwoFactorManager::class)
        ->set('currentPassword', 'wrong-password')
        ->call('disable')
        ->assertHasErrors(['currentPassword']);

    expect($user->refresh()->hasTwoFactorEnabled())->toBeTrue();
    expect($user->two_factor_secret)->not->toBeNull();
});

it('cannot disable 2fa without providing the current password', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    Livewire::actingAs($user)
        ->test(TwoFactorManager::class)
        ->set('currentPassword', '')
        ->call('disable')
        ->assertHasErrors(['currentPassword' => 'required']);

    expect($user->refresh()->hasTwoFactorEnabled())->toBeTrue();
});
