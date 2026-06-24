<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SiteView;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:purge-site-views {--days=90 : Dias de retenção dos registros de visita}')]
#[Description('Remove registros antigos de visitas ao site (site_views) para retenção mínima de dados (LGPD)')]
final class PurgeSiteViewsCommand extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Removendo visitas anteriores a {$days} dias (antes de {$cutoffDate->toDateString()})...");

        try {
            $deletedCount = SiteView::query()
                ->where('viewed_at', '<', $cutoffDate)
                ->delete();

            $this->components->info("Removidos {$deletedCount} registros antigos de site_views.");

            Log::info("site_views expurgados. Removidos {$deletedCount} registros anteriores a {$cutoffDate->toDateString()}.");

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Falha ao expurgar site_views: {$e->getMessage()}");
            Log::error("Falha ao expurgar site_views: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
