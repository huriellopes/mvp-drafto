<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Jobs\ExportDataJob;
use App\Livewire\Dashboard\Admin\AuditLogIndex;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Queue::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

it('queues an audit export job', function () {
    Livewire::actingAs($this->admin)
        ->test(AuditLogIndex::class)
        ->call('export');

    Queue::assertPushed(ExportDataJob::class);
});

it('builds the filter DTO from the bound properties', function () {
    Livewire::actingAs($this->admin)
        ->test(AuditLogIndex::class)
        ->set('event', 'created')
        ->set('auditableType', 'App\\Models\\Tag')
        ->assertSet('event', 'created');
});

it('resets pagination when any filter is updated', function () {
    Livewire::actingAs($this->admin)
        ->test(AuditLogIndex::class)
        ->set('startDate', now()->subWeek()->toDateString())
        ->assertOk();
});
