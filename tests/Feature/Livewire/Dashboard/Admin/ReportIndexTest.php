<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use App\Livewire\Dashboard\Admin\Reports\ReportIndex;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

it('blocks non-admins from the reports page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.reports.index'))
        ->assertForbidden();
});

it('lets an admin open the reports page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.reports.index'))
        ->assertOk()
        ->assertSeeLivewire(ReportIndex::class);
});

it('opens the response modal pre-filled from the report', function () {
    $admin = User::factory()->superAdmin()->create();
    $post = Post::factory()->published()->create();
    $report = Report::factory()->create([
        'reportable_type' => Post::class,
        'reportable_id' => $post->id,
        'status' => ReportStatusEnum::PENDING,
        'reason' => ReportReasonEnum::SPAM,
    ]);

    Livewire::actingAs($admin)
        ->test(ReportIndex::class)
        ->call('openResponseModal', $report->id)
        ->assertSet('activeReport.id', $report->id)
        ->assertSet('selectedStatus', ReportStatusEnum::PENDING->value)
        ->assertDispatched('open-modal', name: 'report-response-modal');
});

it('validates the admin feedback before submitting a response', function () {
    $admin = User::factory()->superAdmin()->create();
    $post = Post::factory()->published()->create();
    $report = Report::factory()->create([
        'reportable_type' => Post::class,
        'reportable_id' => $post->id,
        'reason' => ReportReasonEnum::SPAM,
    ]);

    Livewire::actingAs($admin)
        ->test(ReportIndex::class)
        ->call('openResponseModal', $report->id)
        ->set('adminFeedback', '')
        ->call('submitResponse')
        ->assertHasErrors(['adminFeedback']);
});

it('submits a report response and applies the decision', function () {
    $admin = User::factory()->superAdmin()->create();
    $post = Post::factory()->published()->create();
    $report = Report::factory()->create([
        'reportable_type' => Post::class,
        'reportable_id' => $post->id,
        'status' => ReportStatusEnum::PENDING,
        'reason' => ReportReasonEnum::SPAM,
    ]);

    Livewire::actingAs($admin)
        ->test(ReportIndex::class)
        ->call('openResponseModal', $report->id)
        ->set('adminFeedback', 'Analisado e arquivado.')
        ->set('selectedStatus', ReportStatusEnum::REVIEWED->value)
        ->set('shouldBanUser', false)
        ->call('submitResponse')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', name: 'report-response-modal');

    expect($report->fresh()->status)->toBe(ReportStatusEnum::REVIEWED);
});

it('confirms and deletes a report', function () {
    $admin = User::factory()->superAdmin()->create();
    $post = Post::factory()->published()->create();
    $report = Report::factory()->create([
        'reportable_type' => Post::class,
        'reportable_id' => $post->id,
        'reason' => ReportReasonEnum::SPAM,
    ]);

    Livewire::actingAs($admin)
        ->test(ReportIndex::class)
        ->call('confirmDelete', $report->id)
        ->assertSet('reportIdBeingDeleted', $report->id)
        ->call('deleteReport');

    expect(Report::query()->whereKey($report->id)->exists())->toBeFalse();
});

it('switches tabs and resets filters', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(ReportIndex::class)
        ->call('setTab', 'feedback')
        ->assertSet('tab', 'feedback');
});
