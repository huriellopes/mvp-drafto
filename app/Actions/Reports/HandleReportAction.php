<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\DTOs\HandleReportData;
use App\Enums\UserStatusEnum;
use App\Models\Report;
use App\Models\User;
use App\Notifications\Admin\UserBlockedNotification;
use App\Notifications\Reports\ReportFeedbackNotification;
use App\Notifications\Reports\UserBannedNotification;
use Illuminate\Support\Carbon;
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

                    // DISPARO DE E-MAIL (Via Queue)
                    $offender->notify(new UserBlockedNotification(
                        $data->banReason,
                        $bannedUntil?->format('d/m/Y')
                    ));

                    Log::info("Usuário {$offender->id} banido pelo Admin {$reviewer->id}. Motivo: {$data->banReason}");
                }
            }
        });
    }

    private function resolveReportedUser(Report $report): ?User
    {
        $target = $report->reportable;

        return match (true) {
            $target instanceof User => $target,
            isset($target->author) => $target->author, // Caso de Posts
            isset($target->user) => $target->user,     // Caso de Comentários
            default => null,
        };
    }
}
