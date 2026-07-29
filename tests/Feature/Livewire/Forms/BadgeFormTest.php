<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\DTOs\BadgeData;
use App\Livewire\Dashboard\Profile\ProfileBadgeGenerator;
use App\Livewire\Forms\Dashboard\BadgeForm;
use App\Models\Profile;
use App\Models\User;
use Livewire\Livewire;

it('defaults the badge form to dark theme when the brand theme is unavailable', function () {
    $user = User::factory()->writer()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ProfileBadgeGenerator::class)
        ->assertSet('form.theme', 'dark')
        ->assertSet('form.showStats', true)
        ->assertSet('form.showBio', true);
});

it('exposes the chosen badge options on the host component', function () {
    $user = User::factory()->writer()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)
        ->test(ProfileBadgeGenerator::class)
        ->set('form.theme', 'light')
        ->set('form.showStats', false)
        ->set('form.showLocation', false)
        ->assertSet('form.theme', 'light')
        ->assertSet('form.showStats', false)
        ->assertSet('form.showLocation', false);
});

it('builds a BadgeData DTO from the form state', function () {
    $form = new BadgeForm(new ProfileBadgeGenerator, 'form');
    $form->theme = 'light';
    $form->showStats = false;
    $form->showBio = false;

    $dto = $form->getBadgeData();

    expect($dto)->toBeInstanceOf(BadgeData::class)
        ->and($dto->theme)->toBe('light')
        ->and($dto->showStats)->toBeFalse()
        ->and($dto->showBio)->toBeFalse()
        ->and($dto->showLocation)->toBeTrue();
});
