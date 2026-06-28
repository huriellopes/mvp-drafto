<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Public;

use App\Actions\Public\StoreReportAction;
use App\DTOs\StoreReportData;
use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use App\Models\Post;
use App\Models\Report;

beforeEach(function () {
    $this->action = app(StoreReportAction::class);
});

it('creates a pending report for the given reportable', function () {
    $post = Post::factory()->published()->create();

    $report = $this->action->exec(new StoreReportData(
        reportable_id: $post->id,
        reportable_type: $post->getMorphClass(),
        reason: ReportReasonEnum::cases()[0],
        description: 'Inappropriate content',
    ));

    expect($report)->toBeInstanceOf(Report::class)
        ->and($report->status)->toBe(ReportStatusEnum::PENDING)
        ->and($report->reportable_id)->toBe($post->id)
        ->and($report->description)->toBe('Inappropriate content');

    $this->assertDatabaseHas('reports', [
        'id' => $report->id,
        'status' => ReportStatusEnum::PENDING->value,
    ]);
});
