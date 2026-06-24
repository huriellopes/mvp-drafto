<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Models\Profile;
use App\Models\User;

it('generates a username from the name for profiles without one', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'name' => 'João da Silva',
        'username' => '',
    ]);

    $this->artisan('app:backfill-usernames')->assertSuccessful();

    $profile->refresh();

    expect($profile->username)->not->toBe('')
        ->and($profile->username)->toStartWith('joao-da-silva-');
});

it('does not change profiles that already have a username', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create([
        'user_id' => $user->id,
        'username' => 'existing-user',
    ]);

    $this->artisan('app:backfill-usernames')->assertSuccessful();

    expect($profile->refresh()->username)->toBe('existing-user');
});
