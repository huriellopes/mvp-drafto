<?php

declare(strict_types=1);

namespace Tests\Feature\Accessibility;

use Illuminate\Support\Facades\Blade;

it('hides decorative icons from assistive tech by default', function () {
    $html = Blade::render('<x-lucide-heart class="h-5 w-5" />');

    expect($html)->toContain('aria-hidden="true"');
});

it('allows a call-site to expose a meaningful icon to assistive tech', function () {
    $html = Blade::render('<x-lucide-heart aria-hidden="false" role="img" aria-label="Curtido" />');

    expect($html)
        ->toContain('aria-hidden="false"')
        ->toContain('role="img"')
        ->toContain('aria-label="Curtido"')
        ->not->toContain('aria-hidden="true"');
});
