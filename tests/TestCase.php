<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // A CI não compila os assets do Vite (sem public/build/manifest.json),
        // então desabilitamos o Vite nos testes — não testamos os assets reais.
        $this->withoutVite();
    }
}
