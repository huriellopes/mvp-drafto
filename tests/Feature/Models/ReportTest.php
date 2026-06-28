<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Carbon;

it('casts reason, status and reviewed_at', function () {
    $report = Report::factory()->reviewed()->create();

    expect($report->reason)->toBeInstanceOf(ReportReasonEnum::class)
        ->and($report->status)->toBeInstanceOf(ReportStatusEnum::class)
        ->and($report->reviewed_at)->toBeInstanceOf(Carbon::class);
});

it('resolves the morph-to reportable target', function () {
    $post = Post::factory()->published()->create();
    $report = Report::factory()->forPost($post)->create();

    expect($report->reportable)->toBeInstanceOf(Post::class)
        ->and($report->reportable->id)->toBe($post->id);
});

it('relates to the reporter and reviewer', function () {
    $reporter = User::factory()->active()->create();
    $reviewer = User::factory()->superAdmin()->create();

    $report = Report::factory()
        ->byReporter($reporter)
        ->reviewed($reviewer)
        ->create();

    expect($report->reporter->id)->toBe($reporter->id)
        ->and($report->reviewer->id)->toBe($reviewer->id);
});
