<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BrazilStateEnum;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

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
            try {
                $url = $uf
                    ? self::BASE_URL . "/estados/{$uf}/municipios"
                    : self::BASE_URL . '/municipios';

                $response = $this->getHttpApi($url);

                if ($response->failed()) {
                    Log::warning('API do IBGE retornou erro ao buscar municípios', [
                        'url' => $url,
                        'status' => $response->status(),
                    ]);

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
            } catch (Throwable $e) {
                Log::error('Exceção ao buscar municípios no IBGE', [
                    'message' => $e->getMessage(),
                    'uf' => $uf,
                ]);

                return [];
            }
        });
    }

    public function getUfs(): array
    {
        $cacheKey = 'ibge_ufs';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                $url = self::BASE_URL . '/estados';

                $response = $this->getHttpApi($url);

                if ($response->successful()) {
                    return collect($response->json())
                        ->map(fn ($estado) => [
                            'id' => $estado['id'],
                            'sigla' => $estado['sigla'],
                            'nome' => $estado['nome'],
                        ])
                        ->sortBy('sigla')
                        ->values()
                        ->toArray();
                }

                Log::warning('API do IBGE retornou erro ao buscar UFs', [
                    'status' => $response->status(),
                ]);
            } catch (Throwable $e) {
                Log::error('Exceção ao buscar UFs no IBGE', [
                    'message' => $e->getMessage(),
                ]);
            }

            return $this->getFallbackUfs();
        });
    }

    private function getHttpApi(string $url): Response
    {
        return Http::timeout(5)
            ->retry(3, 200)
            ->get($url);
    }

    private function getFallbackUfs(): array
    {
        return BrazilStateEnum::forIbgeMock();
    }
}
