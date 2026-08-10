<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Settings;

use App\Jobs\GenerateRecoveryCodesJob;
use App\Livewire\Dashboard\Settings\TwoFactorManager;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PragmaRX\Google2FALaravel\Google2FA;

beforeEach(function () {
    $this->google2fa = app(Google2FA::class);
});

it('enables two factor and shows the qr code and confirmation', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(TwoFactorManager::class)
        ->call('enable')
        ->assertSet('showingQrCode', true)
        ->assertSet('showingConfirmation', true);

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

it('confirms two factor with a valid code', function () {
    $secret = $this->google2fa->generateSecretKey();

    $user = User::factory()->withProfile()->create([
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => null,
        'two_factor_recovery_codes' => ['code-aaa', 'code-bbb'],
    ]);

    $this->actingAs($user);

    $code = $this->google2fa->getCurrentOtp($secret);

    Livewire::test(TwoFactorManager::class)
        ->set('code', $code)
        ->call('confirm')
        ->assertSet('showingQrCode', false)
        ->assertSet('showingConfirmation', false)
        ->assertSet('showingRecoveryCodes', true)
        ->assertSet('code', '');

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

it('does not confirm two factor with an invalid code', function () {
    $secret = $this->google2fa->generateSecretKey();

    $user = User::factory()->withProfile()->create([
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(TwoFactorManager::class)
        ->set('code', '000000')
        ->call('confirm')
        ->assertSet('showingRecoveryCodes', false);

    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

it('disables two factor when the current password is correct', function () {
    $user = User::factory()->withProfile()->create([
        'two_factor_secret' => 'secret',
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => ['code-aaa', 'code-bbb'],
    ]);

    $this->actingAs($user);

    Livewire::test(TwoFactorManager::class)
        ->set('showingRecoveryCodes', true)
        ->set('currentPassword', 'password')
        ->call('disable')
        ->assertSet('showingQrCode', false)
        ->assertSet('showingConfirmation', false)
        ->assertSet('showingRecoveryCodes', false);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

it('does not disable two factor when the current password is wrong', function () {
    $user = User::factory()->withProfile()->create([
        'two_factor_secret' => 'secret',
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => ['code-aaa', 'code-bbb'],
    ]);

    $this->actingAs($user);

    Livewire::test(TwoFactorManager::class)
        ->set('currentPassword', 'wrong-password')
        ->call('disable')
        ->assertHasErrors('currentPassword');

    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

it('toggles the recovery codes visibility', function () {
    $user = User::factory()->withProfile()->create([
        'two_factor_recovery_codes' => ['code-aaa', 'code-bbb'],
    ]);

    $this->actingAs($user);

    Livewire::test(TwoFactorManager::class)
        ->call('showRecoveryCodes')
        ->assertSet('showingRecoveryCodes', true)
        ->call('showRecoveryCodes')
        ->assertSet('showingRecoveryCodes', false);
});

it('dispatches a job to download recovery codes', function () {
    Queue::fake();

    $user = User::factory()->withProfile()->create([
        'two_factor_recovery_codes' => ['code-aaa', 'code-bbb'],
    ]);

    $this->actingAs($user);

    $component = Livewire::test(TwoFactorManager::class)
        ->call('downloadRecoveryCodes', 'txt')
        ->assertSet('generatingFormat', 'txt');

    expect($component->get('generatedPath'))->toContain('.txt');

    Queue::assertPushed(GenerateRecoveryCodesJob::class);
});

it('does not dispatch a job when there are no recovery codes', function () {
    Queue::fake();

    $user = User::factory()->withProfile()->create([
        'two_factor_recovery_codes' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(TwoFactorManager::class)
        ->call('downloadRecoveryCodes', 'txt')
        ->assertSet('generatedPath', null);

    Queue::assertNothingPushed();
});

it('reports the generated file readiness', function () {
    Storage::fake('local');

    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    $component = Livewire::test(TwoFactorManager::class);

    expect($component->get('isFileReady'))->toBeFalse();

    Storage::disk('local')->put('temp/ready.txt', 'codes');

    $component->set('generatedPath', 'temp/ready.txt');

    expect($component->get('isFileReady'))->toBeTrue();
});

it('clears the generated file state', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(TwoFactorManager::class)
        ->set('generatedPath', 'temp/x.txt')
        ->set('generatingFormat', 'txt')
        ->call('clearGeneratedFile')
        ->assertSet('generatedPath', null)
        ->assertSet('generatingFormat', null);
});

it('renders the two factor manager', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(TwoFactorManager::class)
        ->assertStatus(200);
});
