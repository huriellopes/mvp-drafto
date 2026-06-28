<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\User;

it('renders the badge frame for an existing profile', function () {
    $user = User::factory()->withProfile()->create();
    $username = $user->profile->username;

    $this->get(route('public.profile.badge', ['username' => $username]))
        ->assertOk();
});

it('removes the x-frame-options header so it can be embedded', function () {
    $user = User::factory()->withProfile()->create();
    $username = $user->profile->username;

    $response = $this->get(route('public.profile.badge', ['username' => $username]));

    $response->assertOk();
    expect($response->headers->get('X-Frame-Options'))->toBeNull()
        ->and($response->headers->get('Content-Security-Policy'))->toContain('frame-ancestors');
});

it('returns 404 for an unknown profile username', function () {
    $this->get(route('public.profile.badge', ['username' => 'does-not-exist']))
        ->assertStatus(404);
});
