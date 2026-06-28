<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Livewire\Dashboard\Admin\Support\SupportIndex;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
});

/**
 * Covers the early return of saveResponse() with no ticket selected (line 50-51).
 */
it('returns silently from saveResponse without a selected ticket', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(SupportIndex::class)
        ->call('saveResponse')
        ->assertOk();
});
