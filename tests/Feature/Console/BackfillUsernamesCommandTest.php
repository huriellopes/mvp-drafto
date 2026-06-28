<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Profile;
use App\Models\User;

it('generates usernames for profiles missing one', function () {
    $user = User::factory()->withProfile()->create(['name' => 'Ada Lovelace']);

    // O boot do Profile gera um username automaticamente, então o esvaziamos
    // diretamente no banco (sem disparar eventos) para simular o backfill.
    // A coluna é NOT NULL, portanto usamos string vazia (também coberta pelo comando).
    Profile::query()->whereKey($user->profile->id)->update(['username' => '']);

    $this->artisan('app:backfill-usernames')
        ->assertExitCode(0);

    $username = Profile::query()->whereKey($user->profile->id)->value('username');

    expect($username)->not->toBeNull()
        ->and($username)->not->toBe('');
});

it('reports success when no profiles need a username', function () {
    User::factory()->withProfile()->create();

    $this->artisan('app:backfill-usernames')
        ->expectsOutputToContain('Nenhum perfil sem username encontrado.')
        ->assertExitCode(0);
});
