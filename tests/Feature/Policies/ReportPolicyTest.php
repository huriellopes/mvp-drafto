<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Report;
use App\Models\User;
use App\Policies\ReportPolicy;

beforeEach(function (): void {
    $this->policy = new ReportPolicy();
});

it('before grants everything to super admin', function (): void {
    expect($this->policy->before(User::factory()->superAdmin()->create(), 'delete'))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    expect($this->policy->before(User::factory()->writer()->create(), 'view'))->toBeNull();
});

it('viewAny allows admin and denies non admin', function (): void {
    expect($this->policy->viewAny(User::factory()->superAdmin()->create()))->toBeTrue();
    expect($this->policy->viewAny(User::factory()->writer()->create()))->toBeFalse();
});

it('view allows the reporter only', function (): void {
    $reporter = User::factory()->active()->create();
    $report = Report::factory()->byReporter($reporter)->create();

    expect($this->policy->view($reporter, $report))->toBeTrue();
    expect($this->policy->view(User::factory()->active()->create(), $report))->toBeFalse();
});

it('create allows active user and denies suspended', function (): void {
    expect($this->policy->create(User::factory()->active()->create()))->toBeTrue();
    expect($this->policy->create(User::factory()->suspended()->create()))->toBeFalse();
});

it('update always denies', function (): void {
    $report = Report::factory()->create();

    expect($this->policy->update(User::factory()->writer()->create(), $report))->toBeFalse();
});

it('delete only allows super admin (before handles allow path)', function (): void {
    $report = Report::factory()->create();

    expect($this->policy->delete(User::factory()->superAdmin()->create(), $report))->toBeTrue();
    expect($this->policy->delete(User::factory()->writer()->create(), $report))->toBeFalse();
});

it('review allows admin when report not yet actioned', function (): void {
    $report = Report::factory()->pending()->create();

    expect($this->policy->review(User::factory()->superAdmin()->create(), $report))->toBeTrue();
});

it('review denies admin when report already actioned', function (): void {
    $report = Report::factory()->actionTaken()->create();

    expect($this->policy->review(User::factory()->superAdmin()->create(), $report))->toBeFalse();
});

it('review denies non admin', function (): void {
    $report = Report::factory()->pending()->create();

    expect($this->policy->review(User::factory()->writer()->create(), $report))->toBeFalse();
});
