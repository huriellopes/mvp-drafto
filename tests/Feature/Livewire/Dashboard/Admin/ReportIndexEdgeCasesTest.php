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

/**
 * Covers the feedback-report branch of openResponseModal() (lines 67-68):
 * praise/suggestion reports force shouldBanUser to false.
 */
it('disables the ban flag when opening a praise report', function () {
    $admin = User::factory()->superAdmin()->create();
    $post = Post::factory()->published()->create();
    $report = Report::factory()->create([
        'reportable_type' => Post::class,
        'reportable_id' => $post->id,
        'status' => ReportStatusEnum::PENDING,
        'reason' => ReportReasonEnum::PRAISE,
    ]);

    Livewire::actingAs($admin)
        ->test(ReportIndex::class)
        ->set('shouldBanUser', true)
        ->call('openResponseModal', $report->id)
        ->assertSet('shouldBanUser', false)
        ->assertSet('activeReport.id', $report->id);
});
