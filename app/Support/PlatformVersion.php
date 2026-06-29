<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Throwable;

final class PlatformVersion
{
    /**
     * Versão atual da plataforma.
     *
     * Ordem de resolução:
     *  1. config('app.version') (APP_VERSION) — fonte oficial em produção;
     *  2. última tag git (conveniência em dev local), cacheada;
     *  3. 'dev'.
     */
    public static function current(): string
    {
        $configured = (string) (config('app.version') ?? '');

        if ($configured !== '' && $configured !== 'dev') {
            return $configured;
        }

        return Cache::remember(
            'platform_version',
            now()->addHours(6),
            static fn (): string => self::fromGitTag() ?? 'dev',
        );
    }

    /**
     * Tenta obter a última tag git do repositório (apenas onde .git existe).
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
            // Sem git disponível / falha ao executar — usa o fallback.
        }

        return null;
    }
}
