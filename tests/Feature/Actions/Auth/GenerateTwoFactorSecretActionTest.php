<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\GenerateTwoFactorSecretAction;
use App\Models\User;

beforeEach(function () {
    $this->action = app(GenerateTwoFactorSecretAction::class);
});

it('generates and persists a two factor secret', function () {
    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);

    $this->action->exec($user);

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull();
});

it('generates eight recovery codes', function () {
    $user = User::factory()->create();

    $this->action->exec($user);

    $codes = $user->fresh()->two_factor_recovery_codes;

    if (is_string($codes)) {
        $codes = json_decode($codes, true);
    }

    expect($codes)->toBeArray()->toHaveCount(8)
        ->and($codes[0])->toMatch('/^.{10}-.{10}$/');
});
