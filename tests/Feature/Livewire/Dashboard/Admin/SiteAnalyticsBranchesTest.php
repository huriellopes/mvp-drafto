<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Jobs\ExportDataJob;
use App\Livewire\Dashboard\Admin\Analytics\SiteAnalytics;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Queue::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

it('queues a site analytics export job', function () {
    Livewire::actingAs($this->admin)
        ->test(SiteAnalytics::class)
        ->call('export');

    Queue::assertPushed(ExportDataJob::class);
});

it('recomputes analytics with a custom date range', function () {
    Livewire::actingAs($this->admin)
        ->test(SiteAnalytics::class)
        ->set('startDate', now()->subDays(10)->toDateString())
        ->set('endDate', now()->toDateString())
        ->assertOk();
});
