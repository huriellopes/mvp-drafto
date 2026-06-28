<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Reports;

use App\Actions\Reports\ListReportsAction;
use App\DTOs\ReportFilterData;
use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use App\Models\Report;
use App\Models\User;

beforeEach(function () {
    $this->action = app(ListReportsAction::class);
});

it('paginates reports with relations eager loaded', function () {
    Report::factory()->count(3)->create();

    $result = $this->action->exec(new ReportFilterData());

    expect($result->total())->toBe(3)
        ->and($result->first()->relationLoaded('reporter'))->toBeTrue();
});

it('filters reports by status', function () {
    Report::factory()->pending()->create();
    Report::factory()->dismissed()->create();

    $result = $this->action->exec(new ReportFilterData(status: ReportStatusEnum::DISMISSED->value));

    expect($result->total())->toBe(1)
        ->and($result->first()->status)->toBe(ReportStatusEnum::DISMISSED);
});

it('filters reports by reason', function () {
    $reason = ReportReasonEnum::cases()[0];
    Report::factory()->reason($reason)->create();
    Report::factory()->reason(ReportReasonEnum::cases()[1])->create();

    $result = $this->action->exec(new ReportFilterData(reason: $reason->value));

    expect($result->total())->toBe(1);
});

it('filters reports by reporter name search', function () {
    $reporter = User::factory()->create(['name' => 'Searchable Reporter']);
    Report::factory()->byReporter($reporter)->create();
    Report::factory()->create();

    $result = $this->action->exec(new ReportFilterData(search: 'Searchable Reporter'));

    expect($result->total())->toBe(1);
});
