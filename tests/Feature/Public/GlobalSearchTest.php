<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Livewire\Public\Site\GlobalSearch;
use App\Models\Profile;
use App\Models\User;
use Livewire\Livewire;

it('finds a writer by name', function () {
    $writer = User::factory()->writer()->create(['name' => 'Mariana Escritora']);
    Profile::factory()->for($writer)->create(['username' => 'mariana_escritora']);

    Livewire::test(GlobalSearch::class)
        ->set('search', 'Mariana')
        ->assertOk()
        ->assertSee('Mariana Escritora')
        ->assertSee('mariana_escritora');
});

it('renders the writer avatar from storage when an avatar exists', function () {
    $writer = User::factory()->writer()->create(['name' => 'Mariana Escritora']);
    Profile::factory()->for($writer)->create([
        'username' => 'mariana_escritora',
        'avatar_path' => 'avatars/mariana.png',
    ]);

    Livewire::test(GlobalSearch::class)
        ->set('search', 'Mariana')
        ->assertOk()
        ->assertSee('avatars/mariana.png', escape: false);
});

it('falls back to initials when the writer has no avatar', function () {
    $writer = User::factory()->writer()->create(['name' => 'Mariana Escritora']);
    Profile::factory()->for($writer)->create([
        'username' => 'mariana_escritora',
        'avatar_path' => null,
    ]);

    Livewire::test(GlobalSearch::class)
        ->set('search', 'Mariana')
        ->assertOk()
        ->assertSee('ME')
        ->assertDontSee('src=""', escape: false);
});

it('returns nothing for terms shorter than two characters', function () {
    User::factory()->writer()->create(['name' => 'Mariana Escritora']);

    Livewire::test(GlobalSearch::class)
        ->set('search', 'M')
        ->assertOk()
        ->assertDontSee('Mariana Escritora');
});
