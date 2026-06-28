<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Reports;

use App\Actions\Reports\HandleReportAction;
use App\DTOs\HandleReportData;
use App\Enums\ReportStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Notifications\Reports\ReportFeedbackNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->action = app(HandleReportAction::class);
});

it('updates the report and notifies the reporter', function () {
    $reviewer = User::factory()->superAdmin()->create();
    $reporter = User::factory()->create();
    $report = Report::factory()->pending()->byReporter($reporter)->create();

    $this->action->exec(new HandleReportData(
        reportId: $report->id,
        status: ReportStatusEnum::REVIEWED,
        feedback: 'Thanks for reporting',
    ), $reviewer);

    $report->refresh();

    expect($report->status)->toBe(ReportStatusEnum::REVIEWED)
        ->and($report->admin_feedback)->toBe('Thanks for reporting')
        ->and($report->reviewed_by)->toBe($reviewer->id)
        ->and($report->reviewed_at)->not->toBeNull();

    Notification::assertSentTo($reporter, ReportFeedbackNotification::class);
});

it('bans the reported post author when requested', function () {
    $reviewer = User::factory()->superAdmin()->create();
    $offender = User::factory()->active()->create();
    $post = Post::factory()->published()->create(['user_id' => $offender->id]);
    $report = Report::factory()->pending()->forPost($post)->create();

    $this->action->exec(new HandleReportData(
        reportId: $report->id,
        status: ReportStatusEnum::ACTION_TAKEN,
        feedback: 'Handled',
        shouldBanUser: true,
        banReason: 'Repeated violations',
        banDays: 7,
    ), $reviewer);

    $offender->refresh();

    expect($offender->status)->toBe(UserStatusEnum::BANNED)
        ->and($offender->ban_reason)->toBe('Repeated violations')
        ->and($offender->banned_until)->not->toBeNull();
});

it('applies a permanent ban when banDays is zero', function () {
    $reviewer = User::factory()->superAdmin()->create();
    $offender = User::factory()->active()->create();
    $post = Post::factory()->published()->create(['user_id' => $offender->id]);
    $report = Report::factory()->pending()->forPost($post)->create();

    $this->action->exec(new HandleReportData(
        reportId: $report->id,
        status: ReportStatusEnum::ACTION_TAKEN,
        feedback: 'Handled',
        shouldBanUser: true,
        banReason: 'Severe',
        banDays: 0,
    ), $reviewer);

    $offender->refresh();

    expect($offender->status)->toBe(UserStatusEnum::BANNED)
        ->and($offender->banned_until)->toBeNull();
});
