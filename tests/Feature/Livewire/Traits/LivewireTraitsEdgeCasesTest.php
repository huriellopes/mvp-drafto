<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Traits;

use App\Jobs\ExportDataJob;
use App\Livewire\Dashboard\Admin\Users\UserIndex;
use App\Livewire\Dashboard\Posts\IndexPosts;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

/**
 * Covers WithBackgroundExport::clearGeneratedFile() (line 33): resetting the
 * generated path state back to null.
 */
it('clears the generated export file path', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('generatedPath', 'temp/export.xlsx')
        ->assertSet('generatedPath', 'temp/export.xlsx')
        ->call('clearGeneratedFile')
        ->assertSet('generatedPath', null);
});

/**
 * Covers the early return of ManagesPostCollections::toggleCollectionForPost()
 * (lines 36-37): nothing happens when no post is targeted.
 */
it('returns silently from toggleCollectionForPost without a target post', function () {
    $writer = User::factory()->writer()->withProfile()->create();

    Livewire::actingAs($writer)
        ->test(IndexPosts::class)
        ->call('toggleCollectionForPost', 1)
        ->assertOk();
});

it('stores the export in the admin own directory and queues the job', function () {
    Queue::fake();
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('export')
        ->assertSet('generatedPath', fn (string $path): bool => str_starts_with($path, "temp/exports/{$admin->id}/usuarios-drafto-"));

    Queue::assertPushedOn('exports', ExportDataJob::class);
});

it('exposes echo listeners for the authenticated user', function () {
    $admin = User::factory()->superAdmin()->create();

    $component = Livewire::actingAs($admin)->test(UserIndex::class)->instance();
    $listeners = (fn () => $this->getListeners())->call($component);

    expect($listeners)
        ->toHaveKey("echo-private:App.Models.User.{$admin->id},.export.ready")
        ->toHaveKey("echo-private:App.Models.User.{$admin->id},.export.failed");
});

it('has no echo listeners for guests', function () {
    $component = new UserIndex;
    $listeners = (fn () => $this->getListeners())->call($component);

    expect($listeners)->toBe([]);
});

it('re-renders when the export is ready', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('generatedPath', "temp/exports/{$admin->id}/a.xlsx")
        ->call('onExportReady', ['path' => "temp/exports/{$admin->id}/a.xlsx"])
        ->assertSet('generatedPath', "temp/exports/{$admin->id}/a.xlsx");
});

it('clears the pending export when it fails', function () {
    $admin = User::factory()->superAdmin()->create();
    $path = "temp/exports/{$admin->id}/a.xlsx";

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('generatedPath', $path)
        ->call('onExportFailed', ['path' => $path])
        ->assertSet('generatedPath', null);
});

it('ignores failures of a different export', function () {
    $admin = User::factory()->superAdmin()->create();
    $path = "temp/exports/{$admin->id}/a.xlsx";

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->set('generatedPath', $path)
        ->call('onExportFailed', ['path' => "temp/exports/{$admin->id}/other.xlsx"])
        ->assertSet('generatedPath', $path);
});
