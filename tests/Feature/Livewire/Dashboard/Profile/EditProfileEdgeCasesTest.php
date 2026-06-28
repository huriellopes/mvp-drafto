<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Profile;

use App\Actions\Profile\UpdateProfileAction;
use App\Livewire\Dashboard\Profile\EditProfile;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use RuntimeException;

beforeEach(function () {
    Http::fake(['*' => Http::response([], 200)]);
});

/**
 * Covers the mount() location-split branch (lines 46-48): a location stored as
 * "City, UF" pre-fills selectedUf from the trailing part.
 */
it('extracts the selected uf from a comma-separated location on mount', function () {
    $user = User::factory()->writer()->withProfile()->create();
    $user->profile->update(['location' => 'Campinas, SP']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->assertSet('selectedUf', 'SP');
});

/**
 * Covers the Throwable catch in save() (lines 211-214): a non-validation
 * failure inside the update action is re-thrown after the error toaster.
 */
it('re-throws a generic failure when saving the profile', function () {
    $user = User::factory()->writer()->withProfile()->create();

    // UpdateProfileAction é final (não pode ser mockado pelo Mockery); ligamos o
    // abstract a um stub que apenas implementa exec() e lança a exceção.
    app()->bind(UpdateProfileAction::class, fn () => new class()
    {
        public function exec(...$args): never
        {
            throw new RuntimeException('Falha inesperada');
        }
    });

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'Nome Válido')
        ->set('form.username', 'nomevalido')
        ->set('form.email', 'valido@example.com')
        ->call('save')
        ->assertHasNoErrors();
})->throws(RuntimeException::class);
