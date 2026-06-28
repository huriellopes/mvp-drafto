<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Reports;

use App\Actions\Reports\HandleReportAction;
use App\DTOs\HandleReportData;
use App\Enums\ReportStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Comment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->action = app(HandleReportAction::class);
});

it('bans the comment author when banning is requested on a reported comment', function () {
    $reviewer = User::factory()->superAdmin()->create();
    $offender = User::factory()->writer()->create();

    $comment = Comment::factory()->create(['user_id' => $offender->id]);

    $report = Report::factory()->create([
        'reportable_type' => $comment->getMorphClass(),
        'reportable_id' => $comment->id,
        'status' => ReportStatusEnum::PENDING,
    ]);

    $this->action->exec(new HandleReportData(
        reportId: $report->id,
        status: ReportStatusEnum::ACTION_TAKEN,
        feedback: 'Comentário inadequado.',
        shouldBanUser: true,
        banReason: 'Toxicidade',
        banDays: 7,
    ), $reviewer);

    expect($offender->fresh()->status)->toBe(UserStatusEnum::BANNED)
        ->and($offender->fresh()->banned_until)->not->toBeNull();
});
