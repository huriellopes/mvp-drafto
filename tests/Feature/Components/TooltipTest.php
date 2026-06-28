<?php

declare(strict_types=1);

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;

it('wraps its slot and exposes the tooltip text', function () {
    $html = Blade::render(
        '<x-ui.tooltip text="Excluir"><button type="button">x</button></x-ui.tooltip>',
    );

    expect($html)->toContain('Excluir')
        ->and($html)->toContain('<button type="button">x</button>')
        ->and($html)->toContain('relative inline-block'); // wrapper padrão
});

it('allows overriding the wrapper class for absolutely positioned buttons', function () {
    $html = Blade::render(
        '<x-ui.tooltip text="Fechar" wrapper-class="absolute right-4 top-4 z-10"><button>x</button></x-ui.tooltip>',
    );

    expect($html)->toContain('absolute right-4 top-4 z-10')
        ->and($html)->not->toContain('relative inline-block');
});
