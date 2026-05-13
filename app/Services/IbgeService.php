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

                if ($response->successful()) {
                    return collect($response->json())
                        ->map(fn ($municipio) => [
                            'id' => $municipio['id'],
                            'nome' => $municipio['nome'],
                        ])
                        ->sortBy('nome')
                        ->values()
                        ->toArray();
                }

                Log::warning('API do IBGE retornou erro ao buscar municípios', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
            } catch (Throwable $e) {
                Log::error('Exceção ao buscar municípios no IBGE', [
                    'message' => $e->getMessage(),
                    'uf' => $uf,
                ]);
            }

            return $this->getFallbackMunicipios($uf);
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
        return Http::timeout(2)
            ->retry(1, 100)
            ->get($url);
    }

    private function getFallbackUfs(): array
    {
        return BrazilStateEnum::forIbgeMock();
    }

    /**
     * Retorna a capital do estado como fallback se a API falhar.
     */
    private function getFallbackMunicipios(?string $uf): array
    {
        if (!$uf) {
            return [];
        }

        $fallbackData = [
            'AC' => ['Rio Branco', 'Cruzeiro do Sul', 'Sena Madureira'],
            'AL' => ['Maceió', 'Arapiraca', 'Rio Largo'],
            'AP' => ['Macapá', 'Santana', 'Laranjal do Jari'],
            'AM' => ['Manaus', 'Parintins', 'Itacoatiara'],
            'BA' => ['Salvador', 'Feira de Santana', 'Vitória da Conquista', 'Camaçari'],
            'CE' => ['Fortaleza', 'Caucaia', 'Juazeiro do Norte'],
            'DF' => ['Brasília'],
            'ES' => ['Vitória', 'Vila Velha', 'Serra', 'Cariacica'],
            'GO' => ['Goiânia', 'Aparecida de Goiânia', 'Anápolis'],
            'MA' => ['São Luís', 'Imperatriz', 'São José de Ribamar'],
            'MT' => ['Cuiabá', 'Várzea Grande', 'Rondonópolis'],
            'MS' => ['Campo Grande', 'Dourados', 'Três Lagoas'],
            'MG' => ['Belo Horizonte', 'Uberlândia', 'Contagem', 'Juiz de Fora'],
            'PA' => ['Belém', 'Ananindeua', 'Santarém'],
            'PB' => ['João Pessoa', 'Campina Grande', 'Santa Rita'],
            'PR' => ['Curitiba', 'Londrina', 'Maringá', 'Ponta Grossa'],
            'PE' => ['Recife', 'Jaboatão dos Guararapes', 'Olinda', 'Caruaru'],
            'PI' => ['Teresina', 'Parnaíba', 'Picos'],
            'RJ' => ['Rio de Janeiro', 'São Gonçalo', 'Duque de Caxias', 'Niterói'],
            'RN' => ['Natal', 'Mossoró', 'Parnamirim'],
            'RS' => ['Porto Alegre', 'Caxias do Sul', 'Canoas', 'Pelotas'],
            'RO' => ['Porto Velho', 'Ji-Paraná', 'Ariquemes'],
            'RR' => ['Boa Vista', 'Rorainópolis'],
            'SC' => ['Florianópolis', 'Joinville', 'Blumenau', 'São José'],
            'SP' => ['São Paulo', 'Guarulhos', 'Campinas', 'São Bernardo do Campo', 'Santo André', 'Ribeirão Preto'],
            'SE' => ['Aracaju', 'Nossa Senhora do Socorro', 'Lagarto'],
            'TO' => ['Palmas', 'Araguaína', 'Gurupi'],
        ];

        $cities = $fallbackData[mb_strtoupper($uf)] ?? ['Outra'];

        return collect($cities)->map(fn ($nome, $index) => [
            'id' => $index,
            'nome' => $nome,
        ])->toArray();
    }
}
