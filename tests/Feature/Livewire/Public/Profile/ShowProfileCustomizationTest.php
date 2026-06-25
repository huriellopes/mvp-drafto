<?php

declare(strict_types=1);

use App\Enums\ProfileVisibilityEnum;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

// O componente público cacheia o usuário por username; limpamos entre os testes
// para evitar que um perfil cacheado vaze para outro (array driver não reseta).
beforeEach(fn () => Cache::flush());

/**
 * Garante a integridade do contrato entre o editor "Meu Perfil Público" e a
 * renderização da página pública: cada personalização precisa chegar ao layout
 * (variáveis CSS) e/ou ao HTML renderizado. Se alguém mexer no layoutData ou nos
 * tokens do Tailwind, estes testes quebram.
 */
function makeCustomProfile(array $profileAttrs = [], array $settingsAttrs = []): User
{
    $writer = User::factory()->writer()->withProfile()->create();

    $writer->profile->update(array_merge([
        'visibility' => ProfileVisibilityEnum::PUBLIC,
    ], $profileAttrs));

    $writer->profile->settings()->create($settingsAttrs);

    return $writer->fresh(['profile.settings']);
}

it('injects all profile customization as CSS variables into the public layout', function () {
    $writer = makeCustomProfile(
        ['primary_color' => '#ff0055', 'accent_color' => '#123456'],
        [
            'secondary_color' => '#00ff00',
            'text_color' => '#111111',
            'background_color' => '#fafafa',
            'button_style' => 'rounded-full',
            'font_family' => 'serif',
        ],
    );

    $response = $this->get(route('profile.show', $writer->profile->username));

    $response->assertOk()
        ->assertSee('--profile-primary: #ff0055', false)
        ->assertSee('--profile-accent: #123456', false)
        ->assertSee('--profile-secondary: #00ff00', false)
        ->assertSee('--profile-text: #111111', false)
        ->assertSee('--profile-bg: #fafafa', false)
        ->assertSee('--profile-button-radius: 9999px', false)
        ->assertSee('ui-serif', false);
});

it('falls back to safe defaults in the layout when no settings row exists', function () {
    // Sem linha em profile_settings, o relacionamento withDefault assume os padrões.
    $writer = User::factory()->writer()->withProfile()->create();
    $writer->profile->update(['visibility' => ProfileVisibilityEnum::PUBLIC]);

    $this->get(route('profile.show', $writer->profile->username))
        ->assertOk()
        ->assertSee('--profile-button-radius: 0.375rem', false) // rounded-md
        ->assertSee('--profile-secondary: ', false); // cai para o accent color
});

it('maps button_style to the correct CSS radius', function (string $buttonStyle, string $expected) {
    $writer = makeCustomProfile([], ['button_style' => $buttonStyle]);

    $this->get(route('profile.show', $writer->profile->username))
        ->assertOk()
        ->assertSee("--profile-button-radius: {$expected}", false);
})->with([
    'rounded-md' => ['rounded-md', '0.375rem'],
    'rounded-xl' => ['rounded-xl', '0.75rem'],
    'rounded-full' => ['rounded-full', '9999px'],
    'square' => ['square', '0'],
]);

it('maps font_family to the correct font stack', function (string $fontFamily, string $needle) {
    $writer = makeCustomProfile([], ['font_family' => $fontFamily]);

    $this->get(route('profile.show', $writer->profile->username))
        ->assertOk()
        ->assertSee($needle, false);
})->with([
    'serif' => ['serif', 'ui-serif'],
    'mono' => ['mono', 'ui-monospace'],
    'sans' => ['sans', 'ui-sans-serif'],
]);

it('shows the subscriber and view counters when the toggles are enabled', function () {
    $writer = makeCustomProfile([], [
        'show_subscriber_count' => true,
        'show_view_count' => true,
    ]);

    // Marcadores precisos do badge (a palavra "visitas" também aparece no
    // banner de cookies, então casamos o fechamento do <span> do contador).
    $this->actingAs($writer)
        ->get(route('profile.show', $writer->profile->username))
        ->assertOk()
        ->assertSee('>seguidores</span>', false)
        ->assertSee('>visitas</span>', false)
        ->assertSee('>publicações</span>', false); // contador sempre visível
});

it('hides the subscriber and view counters when the toggles are disabled', function () {
    $writer = makeCustomProfile([], [
        'show_subscriber_count' => false,
        'show_view_count' => false,
    ]);

    $this->actingAs($writer)
        ->get(route('profile.show', $writer->profile->username))
        ->assertOk()
        ->assertDontSee('>seguidores</span>', false)
        ->assertDontSee('>visitas</span>', false)
        ->assertSee('>publicações</span>', false);
});

it('applies the centered layout classes when layout_type is centered', function () {
    $writer = makeCustomProfile([], ['layout_type' => 'centered']);

    $this->actingAs($writer)
        ->get(route('profile.show', $writer->profile->username))
        ->assertOk()
        ->assertSee('md:flex-col md:items-center md:text-center', false);
});

it('marks the html with data-profile-theme so the chosen theme is not stripped by JS', function () {
    $writer = makeCustomProfile(['theme_mode' => 'dark']);

    $response = $this->get(route('profile.show', $writer->profile->username))->assertOk();

    // O atributo sinaliza ao app.js que o tema é definido no servidor (evita o flash).
    $response->assertSee('data-profile-theme="dark"', false);

    $openingHtmlTag = Str::before(Str::after($response->getContent(), '<html'), '>');
    expect($openingHtmlTag)->toContain('dark'); // classe dark renderizada no servidor
});

it('makes the share-profile button follow the profile button_style', function () {
    $writer = makeCustomProfile([], ['button_style' => 'rounded-full']);

    // O gatilho de compartilhar usa rounded-profile-button (mapeado para o button_style),
    // em vez de um raio fixo.
    $html = Blade::render(
        '<x-ui.share-profile :user="$user" />',
        ['user' => $writer],
    );

    expect($html)->toContain('rounded-profile-button');
});

it('colors the share-profile icon with the profile primary color', function () {
    $writer = makeCustomProfile(['primary_color' => '#ff0055']);

    $html = Blade::render('<x-ui.share-profile :user="$user" />', ['user' => $writer]);

    // O ícone deve herdar a cor primária escolhida (não um cinza fixo).
    expect($html)->toContain('text-profile-primary');
});

it('switches the page to dark mode when a dark background color is chosen (system theme)', function () {
    $writer = makeCustomProfile(['theme_mode' => 'system'], ['background_color' => '#111111']);

    $response = $this->get(route('profile.show', $writer->profile->username))->assertOk();

    $htmlTag = Str::before(Str::after($response->getContent(), '<html'), '>');
    expect($htmlTag)->toContain('class="dark"');
    $response->assertSee('--profile-bg: #111111', false);
});

it('keeps light mode when a light background color is chosen (system theme)', function () {
    $writer = makeCustomProfile(['theme_mode' => 'system'], ['background_color' => '#fafafa']);

    $response = $this->get(route('profile.show', $writer->profile->username))->assertOk();

    $htmlTag = Str::before(Str::after($response->getContent(), '<html'), '>');
    expect($htmlTag)->toContain('class="light"')
        ->and($htmlTag)->not->toContain('class="dark"');
});

it('prioritizes the chosen theme over the background color', function () {
    // Tema "light" explícito + fundo escuro: o tema vence e o fundo é ignorado.
    $writer = makeCustomProfile(['theme_mode' => 'light'], ['background_color' => '#111111']);

    $response = $this->get(route('profile.show', $writer->profile->username))->assertOk();

    $htmlTag = Str::before(Str::after($response->getContent(), '<html'), '>');
    expect($htmlTag)->toContain('class="light"')
        ->and($htmlTag)->not->toContain('class="dark"');

    // A cor de fundo é descartada (volta para inherit).
    $response->assertSee('--profile-bg: inherit', false)
        ->assertDontSee('--profile-bg: #111111', false);
});

it('applies the selected card_style to collection cards', function () {
    // card_style "flat" deve chegar até os cards de coleção (regressão do fix).
    $writer = makeCustomProfile([], ['card_style' => 'flat']);

    $collection = PostCollection::factory()->public()->for($writer)->create();
    $post = Post::factory()->published()->for($writer)->create();
    $collection->posts()->attach($post->id);

    $response = $this->actingAs($writer)
        ->get(route('profile.show', $writer->profile->username))
        ->assertOk();

    // Isola o card da coleção pelo slug no href e confirma a classe do estilo "flat".
    $openingTag = Str::before(Str::after($response->getContent(), $collection->slug), '>');
    expect($openingTag)->toContain('bg-zinc-100/50');
});
