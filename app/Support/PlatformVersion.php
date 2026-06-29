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
     * Versão atual da plataforma.
     *
     * Ordem de resolução:
     *  1. config('app.version') (APP_VERSION) — override manual opcional;
     *  2. última tag git local (rápido, sem rede);
     *  3. última release publicada no GitHub (apenas fora de local/testing),
     *     para que produção reflita a release automaticamente sem editar .env;
     *  4. 'dev'.
     *
     * O resultado é cacheado: ~1h em caso de sucesso (a release nova aparece
     * sozinha em até 1h) e ~5min em caso de falha (permite nova tentativa).
     */
    public static function current(): string
    {
        $configured = (string) (config('app.version') ?? '');

        if ($configured !== '' && $configured !== 'dev') {
            return $configured;
        }

        $cached = Cache::get('platform_version');

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $resolved = self::fromGitTag() ?? self::fromGitHub();

        Cache::put(
            'platform_version',
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
     * Não roda em local/testing para evitar chamadas de rede.
     */
    private static function fromGitHub(): ?string
    {
        if (app()->environment(['local', 'testing'])) {
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
