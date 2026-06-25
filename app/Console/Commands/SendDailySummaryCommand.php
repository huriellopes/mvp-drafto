<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Admin\GenerateDailySummaryAction;
use App\DTOs\DailySummaryData;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('drafto:daily-summary')]
#[Description('Envia ao Telegram um resumo das métricas da plataforma nas últimas 24h.')]
final class SendDailySummaryCommand extends Command
{
    public function handle(GenerateDailySummaryAction $action): int
    {
        $summary = $action->exec();

        Log::channel('telegram_support')->info($this->buildMessage($summary), [
            'novos_usuarios' => $summary->newUsers,
            'novas_publicacoes' => $summary->newPosts,
            'novos_comentarios' => $summary->newComments,
            'novos_seguidores' => $summary->newFollowers,
            'novos_inscritos' => $summary->newSubscribers,
            'novas_denuncias' => $summary->newReports,
            'jobs_com_falha' => $summary->failedJobs,
        ]);

        $this->info('Resumo diário enviado ao Telegram.');

        return self::SUCCESS;
    }

    private function buildMessage(DailySummaryData $summary): string
    {
        return implode("\n", [
            '📊 Resumo diário (últimas 24h)',
            "🆕 Novos usuários: {$summary->newUsers}",
            "✍️ Novas publicações: {$summary->newPosts}",
            "💬 Novos comentários: {$summary->newComments}",
            "👥 Novos seguidores: {$summary->newFollowers}",
            "📰 Novos inscritos (newsletter): {$summary->newSubscribers}",
            "🚩 Novas denúncias: {$summary->newReports}",
            "💥 Jobs com falha: {$summary->failedJobs}",
        ]);
    }
}
