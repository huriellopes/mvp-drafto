<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Throwable;

final class PlatformVersion
{
    /**
     * Chave de cache versionada: ao mudar a lógica, basta incrementar o sufixo
     * para que valores antigos (ex.: 'dev' cacheado por código anterior) sejam
     * ignorados automaticamente após o deploy — sem precisar limpar cache.
     */
    private const string CACHE_KEY = 'platform_version_v2';

    /**
     * Versão atual da plataforma.
     *
     * Ordem de resolução:
     *  1. config('app.version') (APP_VERSION) — override manual opcional;
     *  2. última tag git local (rápido, sem rede);
     *  3. última release publicada no GitHub (em qualquer ambiente exceto
     *     local), para que produção reflita a release sozinha — sem editar .env
     *     e sem depender de APP_ENV ser exatamente "production";
     *  4. 'dev'.
     *
     * O resultado é cacheado: ~1h em caso de sucesso (a release nova aparece
     * sozinha em até 1h) e ~5min em caso de falha (permite nova tentativa).
     */
    public static function current(): string
    {
        $configured = (string) (config('app.version', ''));

        if ($configured !== '' && $configured !== 'dev') {
            return $configured;
        }

        $cached = Cache::get(self::CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $resolved = self::fromGitTag() ?? self::fromGitHub();

        Cache::put(
            self::CACHE_KEY,
            $resolved ?? 'dev',
            $resolved !== null ? now()->addHour() : now()->addMinutes(5),
        );

        return $resolved ?? 'dev';
    }

    /**
     * Última tag git do repositório (apenas onde .git existe).
     */
    private static function fromGitTag(): ?string
    {
        if (!is_dir(base_path('.git'))) {
            return null;
        }

        try {
            $result = Process::path(base_path())->run('git describe --tags --abbrev=0');

            if ($result->successful()) {
                $tag = mb_trim($result->output());

                return $tag !== '' ? $tag : null;
            }
        } catch (Throwable) {
            // Sem git disponível / falha ao executar — tenta o próximo método.
        }

        return null;
    }

    /**
     * Última release publicada no GitHub (repositório público).
     * Não roda em ambiente local (evita chamadas de rede em dev); roda em
     * produção/staging/qualquer outro ambiente.
     */
    private static function fromGitHub(): ?string
    {
        if (app()->isLocal()) {
            return null;
        }

        $repo = (string) config('app.github_repo');

        if ($repo === '') {
            return null;
        }

        try {
            $response = Http::timeout(3)
                ->withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => (string) config('app.name', 'app'),
                ])
                ->get("https://api.github.com/repos/{$repo}/releases/latest");

            if ($response->successful()) {
                $tag = $response->json('tag_name');

                return is_string($tag) && $tag !== '' ? $tag : null;
            }
        } catch (Throwable) {
            // Rede indisponível / rate limit — usa o fallback.
        }

        return null;
    }
}
