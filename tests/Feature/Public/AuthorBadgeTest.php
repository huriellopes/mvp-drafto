<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Blade;

it('renders the author badge without throwing when the username is missing', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    // Simula um estado legado em que o perfil está sem username.
    $profile->username = '';
    $user->setRelation('profile', $profile);

    $html = Blade::render(
        '<x-public.author-badge :user="$user" mode="embed" theme="dark" :showStats="false" :showBio="false" />',
        ['user' => $user],
    );

    // Sem username, o link de perfil cai para "#" em vez de lançar UrlGenerationException.
    expect($html)->toContain('id="badge-preview"')
        ->and($html)->toContain('href="#"');
});

it('embeds the logo as a base64 data uri when embedImages is enabled', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    $html = Blade::render(
        '<x-public.author-badge :user="$user" mode="embed" theme="dark" :embedImages="true" :showStats="false" :showBio="false" />',
        ['user' => $user->load('profile')],
    );

    // O logo (marca d'água) é embutido como data URI, evitando fetch/CORS no html-to-image.
    expect($html)->toContain('src="data:image/png;base64,');
});
