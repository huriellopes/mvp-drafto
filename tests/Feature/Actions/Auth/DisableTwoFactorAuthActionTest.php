<?php

declare(strict_types=1);

use App\Actions\Auth\DisableTwoFactorAuthAction;
use App\Models\User;

beforeEach(function () {
    $this->action = new DisableTwoFactorAuthAction;
});

it('clears the two factor authentication columns', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'secret-value',
        'two_factor_recovery_codes' => ['code-1', 'code-2'],
        'two_factor_confirmed_at' => now(),
    ]);

    $this->action->exec($user);

    $fresh = $user->fresh();

    expect($fresh->two_factor_secret)->toBeNull()
        ->and($fresh->two_factor_recovery_codes)->toBeNull()
        ->and($fresh->two_factor_confirmed_at)->toBeNull();
});

it('is idempotent when two factor is already disabled', function () {
    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => null,
    ]);

    $this->action->exec($user);

    $fresh = $user->fresh();

    expect($fresh->two_factor_secret)->toBeNull()
        ->and($fresh->two_factor_recovery_codes)->toBeNull()
        ->and($fresh->two_factor_confirmed_at)->toBeNull();
});
