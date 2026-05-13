<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class IbgeService
{
    private const string BASE_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades';

    private const int CACHE_TTL = 86400; // 24 horas

    /**
     * Busca municípios. Se a UF for informada, usa o endpoint de estados para performance.
     */
    public function getMunicipios(?string $uf = null): array
    {
        $cacheKey = 'ibge_municipios_' . ($uf ?? 'brasil');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($uf) {
            $url = $uf
                ? self::BASE_URL . "/estados/{$uf}/municipios"
                : self::BASE_URL . '/municipios';

            $response = $this->getHttpApi($url);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json())
                ->map(fn ($municipio) => [
                    'id' => $municipio['id'],
                    'nome' => $municipio['nome'],
                ])
                ->sortBy('nome')
                ->values()
                ->toArray();
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
        return Http::timeout(5)
            ->retry(3, 200)
            ->get($url);
    }
}
