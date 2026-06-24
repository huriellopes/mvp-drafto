<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Profile;
use App\Models\User;

it('auto-generates a username from the name when none is provided', function () {
    $user = User::factory()->create();

    $profile = Profile::create([
        'user_id' => $user->id,
        'name' => 'Maria Souza',
    ]);

    expect($profile->username)->not->toBeEmpty()
        ->and($profile->username)->toStartWith('maria-souza-');
});

it('keeps the username when one is explicitly provided', function () {
    $user = User::factory()->create();

    $profile = Profile::create([
        'user_id' => $user->id,
        'name' => 'Maria Souza',
        'username' => 'maria-custom',
    ]);

    expect($profile->username)->toBe('maria-custom');
});
