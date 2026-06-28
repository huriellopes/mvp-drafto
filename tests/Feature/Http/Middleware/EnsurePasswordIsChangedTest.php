<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;

it('forces a user who must change their password to the change page', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($user)->get(route('dashboard.index'))
        ->assertRedirect(route('dashboard.force-password-change'));
});

it('does not redirect the force-password-change page itself', function () {
    $user = User::factory()->create(['must_change_password' => true]);

    $this->actingAs($user)->get(route('dashboard.force-password-change'))
        ->assertOk();
});

it('allows a user who does not need to change their password', function () {
    $user = User::factory()->create(['must_change_password' => false]);

    $this->actingAs($user)->get(route('dashboard.index'))
        ->assertOk();
});
