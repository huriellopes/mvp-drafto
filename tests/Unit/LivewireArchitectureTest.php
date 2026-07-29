<?php

declare(strict_types=1);
use Livewire\Component;
use Livewire\Form;

/**
 * Testes de arquitetura (Pest Arch) para os padrões de Livewire do projeto
 * (ver Skill drafto-conventions). Rodam junto com a suíte Unit — sem banco,
 * rápidos, e travam regressão estrutural sem depender de revisão manual.
 */
arch('componentes Livewire estendem Livewire\Component')
    ->expect('App\Livewire')
    ->classes()
    ->toExtend(Component::class)
    ->ignoring('App\Livewire\Forms');

arch('Livewire Forms estendem Livewire\Form')
    ->expect('App\Livewire\Forms')
    ->classes()
    ->toExtend(Form::class);

arch('nenhum resíduo de debug em componentes Livewire')
    ->expect('App\Livewire')
    ->not->toUse(['dd', 'dump', 'ray', 'var_dump']);
