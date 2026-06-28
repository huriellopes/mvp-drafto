<?php

declare(strict_types=1);

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;

it('renders the help icon and the text passed via prop', function () {
    $html = Blade::render(
        '<x-ui.help-tip title="Visibilidade" text="Define quem pode ver a publicação." />',
    );

    expect($html)->toContain('<svg') // ícone de ajuda renderizado
        ->and($html)->toContain('Visibilidade')
        ->and($html)->toContain('Define quem pode ver a publicação.')
        ->and($html)->toContain('aria-label="Visibilidade"')
        ->and($html)->toContain('role="tooltip"');
});

it('renders rich slot content when no text prop is provided', function () {
    $html = Blade::render(
        '<x-ui.help-tip title="SEO"><p>Conteúdo <strong>rico</strong> de ajuda.</p></x-ui.help-tip>',
    );

    expect($html)->toContain('Conteúdo <strong>rico</strong> de ajuda.')
        ->and($html)->toContain('SEO');
});

it('starts hidden so it does not flash before Alpine initializes', function () {
    $html = Blade::render('<x-ui.help-tip text="Ajuda" />');

    expect($html)->toContain('style="display: none;"')
        ->and($html)->toContain('x-cloak');
});

it('supports a custom icon and panel width', function () {
    $html = Blade::render('<x-ui.help-tip icon="info" panel-class="w-80" text="Ajuda" />');

    expect($html)->toContain('<svg')
        ->and($html)->toContain('w-80');
});
