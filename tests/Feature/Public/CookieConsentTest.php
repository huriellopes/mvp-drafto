<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

it('renders the cookie consent banner and consent manager on public pages', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('window.DraftoConsent', false)
        ->assertSee('Nós valorizamos sua privacidade');
});

it('renders the privacy policy page', function () {
    $this->get(route('pages.privacy'))
        ->assertOk()
        ->assertSee('Política de')
        ->assertSee('Lei nº 13.709/2018');
});
