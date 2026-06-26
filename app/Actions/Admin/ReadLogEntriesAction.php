<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\LogEntryData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Lê e estrutura os logs da aplicação a partir dos arquivos em storage/logs,
 * além de expor os jobs que falharam (tabela failed_jobs).
 */
final class ReadLogEntriesAction
{
    /**
     * Lê no máximo os últimos bytes do arquivo para não estourar memória em logs grandes.
     */
    private const MAX_BYTES = 2_000_000; // ~2 MB

    private const MAX_ENTRIES = 300;

    /**
     * Arquivos de log disponíveis (mais recentes primeiro), apenas os nomes.
     *
     * @return array<int, string>
     */
    public function files(): array
    {
        $files = glob(storage_path('logs/*.log')) ?: [];

        usort($files, fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return array_map('basename', $files);
    }

    /**
     * Entradas de log do arquivo informado, filtradas pelos níveis e mais recentes primeiro.
     *
     * @param  array<int, string>  $levels  níveis em minúsculas (ex.: ['error','critical'])
     * @return Collection<int, LogEntryData>
     */
    public function exec(?string $file, array $levels): Collection
    {
        $file = $this->resolveFile($file);

        if ($file === null) {
            return collect();
        }

        $content = $this->tail(storage_path('logs/' . $file));

        $pattern = '/^\[(\d{4}-\d{2}-\d{2}[ T][\d:]{8}(?:\.\d+)?(?:[+-]\d{2}:\d{2}|Z)?)\]\s+[\w-]+\.([A-Z]+):\s?(.*?)(?=^\[\d{4}-\d{2}-\d{2}[ T]|\z)/sm';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        return collect($matches)
            ->filter(fn (array $m): bool => in_array(mb_strtolower($m[2]), $levels, true))
            ->map(function (array $m): LogEntryData {
                $message = mb_trim($m[3]);
                $lines = explode("\n", $message, 2);

                return new LogEntryData(
                    level: mb_strtolower($m[2]),
                    loggedAt: $m[1],
                    summary: mb_trim($lines[0]),
                    details: mb_trim($lines[1] ?? ''),
                );
            })
            ->reverse() // mais recentes primeiro
            ->take(self::MAX_ENTRIES)
            ->values();
    }

    /**
     * Jobs que falharam (tabela failed_jobs), mais recentes primeiro.
     *
     * @return Collection<int, array{uuid: string, job: string, queue: string, failed_at: string, error: string}>
     */
    public function failedJobs(): Collection
    {
        return DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(100)
            ->get()
            ->map(fn (object $row): array => [
                'uuid' => (string) $row->uuid,
                'job' => $this->jobName($row->payload),
                'queue' => (string) $row->queue,
                'failed_at' => (string) $row->failed_at,
                'error' => mb_trim(explode("\n", (string) $row->exception)[0]),
            ]);
    }

    /**
     * Extrai o nome legível do job a partir do payload serializado.
     */
    private function jobName(mixed $payloadJson): string
    {
        $payload = json_decode((string) $payloadJson, true);

        if (!is_array($payload)) {
            return 'Desconhecido';
        }

        if (isset($payload['displayName']) && is_string($payload['displayName'])) {
            return $payload['displayName'];
        }

        $data = $payload['data'] ?? null;

        if (is_array($data) && isset($data['commandName']) && is_string($data['commandName'])) {
            return $data['commandName'];
        }

        return 'Desconhecido';
    }

    private function resolveFile(?string $file): ?string
    {
        $available = $this->files();

        if ($file !== null && in_array($file, $available, true)) {
            return $file;
        }

        return $available[0] ?? null;
    }

    /**
     * Lê apenas o final do arquivo (últimos MAX_BYTES) e descarta a primeira
     * entrada potencialmente cortada.
     */
    private function tail(string $path): string
    {
        if (!is_file($path)) {
            return '';
        }

        $size = filesize($path) ?: 0;
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return '';
        }

        if ($size > self::MAX_BYTES) {
            fseek($handle, -self::MAX_BYTES, SEEK_END);
        }

        $content = (string) stream_get_contents($handle);
        fclose($handle);

        // Remove um possível pedaço inicial cortado (antes da 1ª linha de entrada).
        if ($size > self::MAX_BYTES) {
            $firstEntry = preg_match('/^\[\d{4}-\d{2}-\d{2}[ T]/m', $content, $mm, PREG_OFFSET_CAPTURE)
                ? $mm[0][1]
                : 0;
            $content = mb_substr($content, $firstEntry);
        }

        return $content;
    }
}
