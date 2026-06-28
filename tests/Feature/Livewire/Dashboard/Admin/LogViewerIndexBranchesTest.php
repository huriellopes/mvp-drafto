<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Livewire\Dashboard\Admin\System\LogViewerIndex;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->superAdmin()->create();
});

it('selects a valid tab', function () {
    Livewire::actingAs($this->admin)
        ->test(LogViewerIndex::class)
        ->call('selectTab', 'debug')
        ->assertSet('tab', 'debug')
        ->call('selectTab', 'jobs')
        ->assertSet('tab', 'jobs');
});

it('falls back to errors for an invalid tab', function () {
    Livewire::actingAs($this->admin)
        ->test(LogViewerIndex::class)
        ->call('selectTab', 'invalid-tab')
        ->assertSet('tab', 'errors');
});

it('renders the debug tab without errors', function () {
    Livewire::actingAs($this->admin)
        ->test(LogViewerIndex::class)
        ->set('tab', 'debug')
        ->assertOk();
});

it('returns silently from retryJob when no uuid is present', function () {
    Livewire::actingAs($this->admin)
        ->test(LogViewerIndex::class)
        ->call('retryJob')
        ->assertOk();
});

it('returns silently from forgetJob when no uuid is present', function () {
    Livewire::actingAs($this->admin)
        ->test(LogViewerIndex::class)
        ->call('forgetJob')
        ->assertOk();
});

it('does not open the detail modal for an unknown job', function () {
    Livewire::actingAs($this->admin)
        ->test(LogViewerIndex::class)
        ->call('showDetail', 'non-existent-uuid')
        ->assertSet('detailJob', null);
});

it('retries a job by uuid using the queue:retry command', function () {
    Artisan::partialMock()
        ->shouldReceive('call')
        ->with('queue:retry', ['id' => ['uuid-retry-1']])
        ->andReturn(0);

    Livewire::actingAs($this->admin)
        ->test(LogViewerIndex::class)
        ->call('confirmRetry', 'uuid-retry-1')
        ->assertSet('actingUuid', 'uuid-retry-1')
        ->call('retryJob')
        ->assertSet('actingUuid', null);
});
