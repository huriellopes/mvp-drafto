<?php

declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\ReportStatusEnum;
use App\Models\Report;
use App\Models\User;
use App\Notifications\Reports\{ReportFeedbackNotification, UserBannedNotification};

final class HandleReportAction
{
    public function exec(
        Report $report,
        User $reviewer,
        ReportStatusEnum $newStatus,
        ?string $feedback = null,
        bool $banUser = false,
        ?string $banReason = null
    ): void {
        // 1. Atualiza a denúncia
        $report->update([
            'status' => $newStatus,
            'admin_feedback' => $feedback,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        // 2. Notifica o denunciante (Reporter)
        if ($report->reporter) {
            $report->reporter->notify(new ReportFeedbackNotification($report));
        }

        // 3. Processa Banimento se ACTION_TAKEN
        if ($banUser && $newStatus === ReportStatusEnum::ACTION_TAKEN) {
            $reportedUser = $this->getReportedUser($report);

            if ($reportedUser) {
                $reportedUser->update([
                    'banned_until' => now()->addDays(30),
                    'ban_reason' => $banReason ?? 'Violação das diretrizes da comunidade.',
                ]);

                $reportedUser->notify(new UserBannedNotification(
                    $reportedUser->banned_until,
                    $reportedUser->ban_reason
                ));
            }
        }
    }

    private function getReportedUser(Report $report): ?User
    {
        $target = $report->reportable;

        if ($target instanceof User) return $target;
        if (isset($target->user_id)) return User::find($target->user_id);
        if (isset($target->user)) return $target->user;

        return null;
    }
}
