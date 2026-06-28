<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\DTOs\HandleReportData;
use App\Enums\UserStatusEnum;
use App\Models\Report;
use App\Models\User;
use App\Notifications\Reports\ReportFeedbackNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class HandleReportAction
{
    /**
     * @throws Throwable
     */
    public function exec(HandleReportData $data, User $reviewer): void
    {
        DB::transaction(function () use ($data, $reviewer) {
            $report = Report::findOrFail($data->reportId);

            // 1. Atualizar Denúncia
            $report->update([
                'status' => $data->status,
                'admin_feedback' => $data->feedback,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            // 2. Notificar Denunciante (Assíncrono via Queue)
            $report->reporter->notify(new ReportFeedbackNotification($report));

            // 3. Processar Banimento
            if ($data->shouldBanUser) {
                $offender = $this->resolveReportedUser($report);

                if ($offender) {
                    $bannedUntil = $data->banDays > 0 ? now()->addDays($data->banDays) : null;

                    $offender->update([
                        'status' => UserStatusEnum::BANNED,
                        'banned_until' => $bannedUntil,
                        'ban_reason' => $data->banReason,
                    ]);

                    Log::info("Usuário {$offender->id} banido pelo Admin {$reviewer->id}. Motivo: {$data->banReason}");
                }
            }
        });
    }

    private function resolveReportedUser(Report $report): ?User
    {
        $target = $report->reportable;

        // Tanto Post quanto Comment expõem a relação `author` (apontando para o
        // autor/comentarista), então ela cobre os dois tipos reportáveis.
        return match (true) {
            $target instanceof User => $target,
            isset($target->author) => $target->author,
            default => null,
        };
    }
}
