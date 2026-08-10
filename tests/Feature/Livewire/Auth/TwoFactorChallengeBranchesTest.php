<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Actions\Auth\GenerateTwoFactorSecretAction;
use App\Livewire\Auth\TwoFactorChallenge;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    // O RateLimiter usa cache, que não é resetado pelo RefreshDatabase entre
    // testes, então limpamos manualmente para evitar vazamento de estado.
    Cache::flush();
});

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

it('blocks further attempts with rate limiting after too many invalid 2fa codes', function () {
    $user = User::factory()->create();
    app(GenerateTwoFactorSecretAction::class)->exec($user);
    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

    session(['auth.2fa.id' => $user->id]);

    $component = Livewire::test(TwoFactorChallenge::class);

    // Consumes the 5 allowed attempts, all with an invalid code.
    for ($i = 0; $i < 5; $i++) {
        $component->set('code', '000000')
            ->call('verify')
            ->assertHasErrors('code');

        expect($component->errors()->first('code'))->toBe('O código informado é inválido.');
    }

    // The 6th attempt must be blocked by the rate limiter, not by the
    // "invalid code" branch — even a code that would otherwise be valid
    // is never checked once the limit is reached.
    $component->set('code', '000000')
        ->call('verify')
        ->assertHasErrors('code');

    expect($component->errors()->first('code'))
        ->not->toBe('O código informado é inválido.')
        ->toContain('Too many');

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
