<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Dashboard\Profile\EditProfile;
use App\Livewire\Forms\Dashboard\ProfileForm;
use App\Models\User;

/**
 * Covers the early return of setUser() when the user has no profile
 * (ProfileForm lines 80-81): the form fields stay at their defaults.
 */
it('returns early from setUser when the user has no profile', function () {
    $user = User::factory()->create(); // sem withProfile()

    expect($user->profile)->toBeNull();

    $this->actingAs($user);

    $form = new ProfileForm(new EditProfile(), 'form');
    $form->setUser($user);

    // Sem perfil, nenhum campo é preenchido — username permanece vazio (default).
    expect($form->username)->toBe('')
        ->and($form->avatar)->toBeNull()
        ->and($form->cover)->toBeNull();
});
