<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Profile;

use App\Livewire\Dashboard\Profile\ProfileBadgeGenerator;
use App\Models\User;
use Livewire\Livewire;

it('renders the badge generator page', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user)
        ->get(route('dashboard.profile.badge'))
        ->assertOk()
        ->assertSeeLivewire(ProfileBadgeGenerator::class);
});

it('defaults the theme on mount', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $theme = Livewire::test(ProfileBadgeGenerator::class)->get('form.theme');

    expect($theme)->toBeIn(['brand', 'dark']);
});

it('exposes the authenticated user with their profile loaded', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    $componentUser = Livewire::test(ProfileBadgeGenerator::class)->get('user');

    expect($componentUser->id)->toBe($user->id)
        ->and($componentUser->relationLoaded('profile'))->toBeTrue();
});

it('renders the badge generator component', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(ProfileBadgeGenerator::class)->assertStatus(200);
});
