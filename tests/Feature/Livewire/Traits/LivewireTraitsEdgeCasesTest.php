<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Traits;

use App\Livewire\Dashboard\Admin\Users\UserIndex;
use App\Livewire\Dashboard\Posts\IndexPosts;
use App\Models\User;
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
