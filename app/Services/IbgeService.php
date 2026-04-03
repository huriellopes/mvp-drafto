<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class IbgeService
{
    private const string BASE_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades';

    private const int CACHE_TTL = 86400;

    public function getMunicipios(?string $uf = null, string $search = ''): array
    {
        $cacheKey = "ibge_municipios_{$uf}_{$search}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($uf, $search) {
            $url = self::BASE_URL . '/municipios';

            $params = [];

            if ($uf) {
                $params['uf'] = $uf;
            }

            if ($search) {
                $params['nome'] = $search;
            }

            $response = $this->getHttpApi($url);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json())
                ->map(fn ($municipio) => [
                    'id' => $municipio['id'],
                    'nome' => $municipio['nome'],
                    'microrregiao' => $municipio['microrregiao']['nome'] ?? null,
                    'mesorregiao' => $municipio['microrregiao']['mesorregiao']['nome'] ?? null,
                    'uf' => [
                        'sigla' => $municipio['microrregiao']['mesorregiao']['UF']['sigla'] ?? null,
                        'nome' => $municipio['microrregiao']['mesorregiao']['UF']['nome'] ?? null,
                    ],
                ])
                ->toArray();
        });
    }

    public function getMunicipio(int $id): ?array
    {
        $cacheKey = "ibge_municipio_{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $url = self::BASE_URL . "/municipios/{$id}";

            $response = $this->getHttpApi($url);

            if ($response->failed()) {
                return;
            }

            $municipio = $response->json();

            return [
                'id' => $municipio['id'],
                'nome' => $municipio['nome'],
                'microrregiao' => $municipio['microrregiao']['nome'] ?? null,
                'mesorregiao' => $municipio['microrregiao']['mesorregiao']['nome'] ?? null,
                'uf' => [
                    'sigla' => $municipio['microrregiao']['mesorregiao']['UF']['sigla'] ?? null,
                    'nome' => $municipio['microrregiao']['mesorregiao']['UF']['nome'] ?? null,
                ],
            ];
        });
    }

    public function getUfs(): array
    {
        $cacheKey = 'ibge_ufs';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $url = self::BASE_URL . '/estados';

            $response = $this->getHttpApi($url);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json())
                ->map(fn ($estado) => [
                    'id' => $estado['id'],
                    'sigla' => $estado['sigla'],
                    'nome' => $estado['nome'],
                ])
                ->sortBy('sigla')
                ->values()
                ->toArray();
        });
    }

    private function getHttpApi(string $url)
    {
        return Http::timeout(3)
            ->retry(2, 100)
            ->get($url);
    }
}
