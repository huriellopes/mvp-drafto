<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Models\User;

it('marks authenticated dashboard pages as noindex', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.account'))
        ->assertOk()
        ->assertSee('name="robots" content="noindex, nofollow"', false);
});

it('does not mark the public home page as noindex', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('noindex');
});
