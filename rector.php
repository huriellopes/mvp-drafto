<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    // Foco no código de produção. tests/ pode ser adicionado numa leva futura.
    ->withPaths([
        __DIR__ . '/app',
    ])
    ->withCache(__DIR__ . '/storage/framework/cache/rector')
    // Conjuntos de qualidade do PHP (alinhados à versão do composer).
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        earlyReturn: true,
        // typeDeclarations: gera muitas mudanças e exige ajustes finos no
        // PHPStan/testes — habilitar numa próxima leva, de forma controlada.
    )
    // Regras específicas do Laravel (idiomas, collections, helpers).
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_COLLECTION,
    ])
    ->withSkip([
        __DIR__ . '/bootstrap/cache',
        // Não alterar assinaturas de métodos públicos (API): remover um
        // parâmetro quebra chamadores por argumento nomeado (ex.: testes).
        RemoveUnusedPublicMethodParameterRector::class,
    ]);
