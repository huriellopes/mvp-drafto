<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Admin\PruneLogFilesAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:prune-logs {--days=3 : Mantém os arquivos de log dos últimos N dias}')]
#[Description('Remove arquivos de log antigos de storage/logs, mantendo apenas os dos últimos dias.')]
final class PruneLogsCommand extends Command
{
    public function handle(PruneLogFilesAction $action): int
    {
        $days = max(1, (int) $this->option('days'));

        $deleted = $action->exec($days);

        $this->info("Logs antigos removidos: {$deleted} (mantidos os últimos {$days} dias).");

        return self::SUCCESS;
    }
}
