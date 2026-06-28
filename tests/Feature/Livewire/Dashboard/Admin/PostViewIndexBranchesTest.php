<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Jobs\ExportDataJob;
use App\Livewire\Dashboard\Admin\PostViews\PostViewIndex;
use App\Models\PostView;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    Queue::fake();
    $this->admin = User::factory()->superAdmin()->create();
});

it('queues a post views export job', function () {
    Livewire::actingAs($this->admin)
        ->test(PostViewIndex::class)
        ->call('export');

    Queue::assertPushed(ExportDataJob::class);
});

it('keeps the same column sorted toggling direction', function () {
    Livewire::actingAs($this->admin)
        ->test(PostViewIndex::class)
        ->call('sortBy', 'viewed_at')
        ->assertSet('direction', 'asc')
        ->call('sortBy', 'viewed_at')
        ->assertSet('direction', 'desc');
});

it('filters views by search term', function () {
    PostView::factory()->count(2)->create();

    Livewire::actingAs($this->admin)
        ->test(PostViewIndex::class)
        ->set('search', 'no-match-term-xyz')
        ->assertOk();
});
