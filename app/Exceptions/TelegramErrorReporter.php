<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Reporta erros da aplicação: persiste em log e alerta no Telegram em tempo real,
 * roteando por severidade e com throttle para evitar flood do mesmo erro.
 *
 * O trace_id (LogContextMiddleware) entra automaticamente no contexto, permitindo
 * correlacionar o alerta, os logs e a tela de erro.
 */
final class TelegramErrorReporter
{
    /**
     * Janela de throttle: no máximo 1 alerta por assinatura de erro neste período.
     */
    private const THROTTLE_MINUTES = 5;

    /**
     * Trechos de path típicos de varredura automatizada de bots (WordPress, .env,
     * phpMyAdmin, probes de RCE, etc.). 404s nesses caminhos são ruído de fundo da
     * internet — não-direcionados e não-acionáveis — então não geram alerta no
     * Telegram (seguem registrados no log em arquivo para auditoria/estatística).
     */
    private const SCANNER_NOISE = [
        'wp-', 'wordpress', 'xmlrpc.php', 'wlwmanifest',
        '.env', '.git', '.svn', '.htaccess', '.aws/',
        'phpmyadmin', 'phpinfo', 'myadmin', 'mysqladmin', '/pma',
        'vendor/phpunit', 'eval-stdin.php', 'cgi-bin', 'shellscript',
        'autodiscover', '.aspx', '.asp', '.jsp', '.cgi',
        'boaform', 'setup.cgi', 'hnap1', 'solr/', 'actuator', 'jenkins',
        '.well-known/traffic-advice', '.well-known/pki-validation',
        '.sql', '.bak', '.old', '.backup',
    ];

    /**
     * @return false sempre — sinaliza ao handler padrão para não logar/alertar de novo.
     */
    public function report(Throwable $e): bool
    {
        $status = $this->statusFor($e);

        // Persistência em arquivo sempre (sem throttle) — histórico completo.
        Log::channel('daily')->error("[{$status}] " . $e->getMessage(), ['exception' => $e]);

        if (filled(config('services.telegram.token')) && $this->shouldAlert($e, $status)) {
            [$channel, $emoji] = $this->routeFor($status);

            Log::channel($channel)->error("{$emoji} Erro {$status}: " . $e->getMessage(), ['exception' => $e]);
        }

        return false;
    }

    private function statusFor(Throwable $e): int
    {
        return $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
    }

    /**
     * Evita flood: só alerta a primeira ocorrência de cada erro dentro da janela.
     * Cache::add é atômico (no Redis), então é seguro sob concorrência.
     */
    private function shouldAlert(Throwable $e, int $status): bool
    {
        if ($this->isScannerNoise($status)) {
            return false;
        }

        $signature = $status . '|' . $e::class . '|' . $e->getFile() . ':' . $e->getLine();

        return Cache::add(
            'error-alert:' . md5($signature),
            true,
            now()->addMinutes(self::THROTTLE_MINUTES),
        );
    }

    /**
     * Um 404 cujo path bate com assinatura de varredura de bot é ruído: não alerta.
     * Só se aplica a 404 — um 500 real continua sendo alertado mesmo na rota probada.
     */
    private function isScannerNoise(int $status): bool
    {
        if ($status !== 404) {
            return false;
        }

        $path = request()?->path();

        if (! is_string($path) || $path === '') {
            return false;
        }

        return Str::contains(Str::lower($path), self::SCANNER_NOISE);
    }

    /**
     * 5xx → canal crítico | 404 → canal verboso (alto volume) | demais 4xx → suporte.
     *
     * @return array{0: string, 1: string}
     */
    private function routeFor(int $status): array
    {
        return match (true) {
            $status >= 500 => ['telegram_alerts', '💥'],
            $status === 404 => ['telegram_debug', '🔍'],
            default => ['telegram_support', '⚠️'],
        };
    }
}
