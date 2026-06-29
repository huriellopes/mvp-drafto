<?php

declare(strict_types=1);

namespace Tests\Feature\Accessibility;

use Illuminate\Support\Facades\Blade;

it('associates input validation errors via aria-invalid and aria-describedby', function () {
    $html = Blade::render(
        '<x-ui.input id="email" label="E-mail" error="Campo obrigatório" />',
    );

    expect($html)->toContain('aria-invalid="true"')
        ->and($html)->toContain('aria-describedby="email-error"')
        ->and($html)->toContain('id="email-error"');
});

it('does not add aria-invalid when there is no input error', function () {
    $html = Blade::render('<x-ui.input id="email" label="E-mail" />');

    expect($html)->not->toContain('aria-invalid');
});

it('associates select and textarea errors with their controls', function () {
    $select = Blade::render('<x-ui.select id="role" label="Papel" error="Inválido"><option>a</option></x-ui.select>');
    $textarea = Blade::render('<x-ui.textarea id="bio" label="Bio" error="Inválido" />');

    expect($select)->toContain('aria-describedby="role-error"')
        ->and($select)->toContain('id="role-error"')
        ->and($textarea)->toContain('aria-describedby="bio-error"')
        ->and($textarea)->toContain('id="bio-error"');
});

it('marks table header cells with scope=col', function () {
    $html = Blade::render('<x-ui.table.th label="Nome" />');

    expect($html)->toContain('scope="col"');
});
