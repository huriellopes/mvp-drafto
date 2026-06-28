<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard;

use App\Enums\ProfileVisibilityEnum;
use App\Livewire\Dashboard\ProfileStatus;
use App\Models\User;
use Livewire\Livewire;

it('exposes the public profile url from the username', function () {
    $user = User::factory()->withProfile()->create();
    $user->profile->update(['username' => 'janedoe']);

    $this->actingAs($user);

    $url = Livewire::test(ProfileStatus::class)->get('profileUrl');

    expect($url)->toBe(route('profile.show', 'janedoe'));
});

it('exposes a shareable profile url', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    $shareUrl = Livewire::test(ProfileStatus::class)->get('shareUrl');

    expect($shareUrl)->toBeString()->not->toBeEmpty();
});

it('exposes the profile visibility status', function () {
    $user = User::factory()->withProfile()->create();
    $user->profile->update(['visibility' => ProfileVisibilityEnum::PUBLIC]);

    $this->actingAs($user);

    $status = Livewire::test(ProfileStatus::class)->get('profileStatus');

    expect($status)->toBe(ProfileVisibilityEnum::PUBLIC);
});

it('exposes the missing fields, completion percentage and completeness', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    $component = Livewire::test(ProfileStatus::class);

    expect($component->get('missingFields'))->toBeArray()
        ->and($component->get('completionPercentage'))->toBeInt()
        ->and($component->get('isComplete'))->toBeBool();
});

it('renders the profile status component', function () {
    $user = User::factory()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(ProfileStatus::class)->assertStatus(200);
});
