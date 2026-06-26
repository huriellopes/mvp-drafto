<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use Illuminate\Support\Carbon;

/**
 * Remove arquivos de log antigos, mantendo apenas os dos últimos N dias.
 */
final class PruneLogFilesAction
{
    /**
     * @param  int  $keepDays  mantém arquivos modificados nos últimos N dias
     * @param  string|null  $directory  diretório dos logs (default: storage/logs)
     * @return int quantidade de arquivos removidos
     */
    public function exec(int $keepDays, ?string $directory = null): int
    {
        $directory ??= storage_path('logs');
        $cutoff = Carbon::now()->subDays(max(1, $keepDays))->getTimestamp();

        $deleted = 0;

        foreach (glob($directory . '/*.log') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < $cutoff && @unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
