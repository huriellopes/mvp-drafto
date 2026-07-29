<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\BrazilStateEnum;
use App\Services\IbgeService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('returns sorted municipios on a successful response for a uf', function () {
    Http::fake([
        '*/estados/SP/municipios' => Http::response([
            ['id' => 2, 'nome' => 'Santos'],
            ['id' => 1, 'nome' => 'Campinas'],
        ], 200),
    ]);

    $service = new IbgeService;

    $result = $service->getMunicipios('SP');

    expect($result)->toBe([
        ['id' => 1, 'nome' => 'Campinas'],
        ['id' => 2, 'nome' => 'Santos'],
    ]);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/estados/SP/municipios'));
});

it('uses the national municipios endpoint when no uf is provided', function () {
    Http::fake([
        '*/municipios' => Http::response([
            ['id' => 10, 'nome' => 'Aracaju'],
        ], 200),
    ]);

    $service = new IbgeService;

    $result = $service->getMunicipios();

    expect($result)->toBe([
        ['id' => 10, 'nome' => 'Aracaju'],
    ]);

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/municipios'));
});

it('caches the municipios result so the api is only called once', function () {
    Http::fake([
        '*' => Http::response([['id' => 1, 'nome' => 'Recife']], 200),
    ]);

    $service = new IbgeService;

    $service->getMunicipios('PE');
    $service->getMunicipios('PE');

    Http::assertSentCount(1);
});

it('falls back to capital cities when the municipios api returns a non-2xx status', function () {
    Http::fake([
        '*' => Http::response('error', 500),
    ]);

    $service = new IbgeService;

    $result = $service->getMunicipios('SP');

    expect($result)->toContain(['id' => 0, 'nome' => 'São Paulo']);
});

it('falls back to capital cities when the municipios request throws (timeout)', function () {
    Http::fake(function () {
        throw new ConnectionException('timeout');
    });

    $service = new IbgeService;

    $result = $service->getMunicipios('RJ');

    expect($result)->toContain(['id' => 0, 'nome' => 'Rio de Janeiro']);
});

it('falls back to the default city for an unknown uf', function () {
    Http::fake(['*' => Http::response('error', 503)]);

    $service = new IbgeService;

    $result = $service->getMunicipios('ZZ');

    expect($result)->toBe([['id' => 0, 'nome' => 'Outra']]);
});

it('returns an empty array fallback for municipios when uf is null and the api fails', function () {
    Http::fake(['*' => Http::response('error', 500)]);

    $service = new IbgeService;

    expect($service->getMunicipios())->toBe([]);
});

it('returns sorted ufs on a successful response', function () {
    Http::fake([
        '*/estados' => Http::response([
            ['id' => 35, 'sigla' => 'SP', 'nome' => 'São Paulo'],
            ['id' => 33, 'sigla' => 'RJ', 'nome' => 'Rio de Janeiro'],
        ], 200),
    ]);

    $service = new IbgeService;

    $result = $service->getUfs();

    expect($result[0]['sigla'])->toBe('RJ')
        ->and($result[1]['sigla'])->toBe('SP');
});

it('caches the ufs result so the api is only called once', function () {
    Http::fake([
        '*' => Http::response([['id' => 35, 'sigla' => 'SP', 'nome' => 'São Paulo']], 200),
    ]);

    $service = new IbgeService;

    $service->getUfs();
    $service->getUfs();

    Http::assertSentCount(1);
});

it('falls back to the BrazilStateEnum mock when the ufs api returns a non-2xx status', function () {
    Http::fake(['*' => Http::response('error', 500)]);

    $service = new IbgeService;

    expect($service->getUfs())->toBe(BrazilStateEnum::forIbgeMock());
});

it('falls back to the BrazilStateEnum mock when the ufs request throws', function () {
    Http::fake(function () {
        throw new ConnectionException('timeout');
    });

    $service = new IbgeService;

    expect($service->getUfs())->toBe(BrazilStateEnum::forIbgeMock());
});
