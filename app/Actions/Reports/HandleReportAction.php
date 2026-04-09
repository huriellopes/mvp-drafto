<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\DTOs\HandleReportData;
use App\Enums\UserStatusEnum;
use App\Models\Report;
use App\Models\User;
use App\Notifications\Reports\ReportFeedbackNotification;
use App\Notifications\Reports\UserBannedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
                $reportedUser = $this->resolveReportedUser($report);

                if ($reportedUser) {
                    $bannedUntil = Carbon::now()->addDays($data->banDays);

                    $reportedUser->update([
                        'status' => UserStatusEnum::BANNED,
                        'banned_until' => $bannedUntil,
                        'ban_reason' => $data->banReason ?? 'Violação das diretrizes da comunidade.',
                    ]);

                    $reportedUser->notify(new UserBannedNotification($bannedUntil, $reportedUser->ban_reason));
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
