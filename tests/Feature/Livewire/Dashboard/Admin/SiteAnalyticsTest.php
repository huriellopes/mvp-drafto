<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Livewire\Dashboard\Admin\Analytics\SiteAnalytics;
use App\Models\User;
use Livewire\Livewire;

it('blocks non-admins from the site analytics page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.analytics.index'))
        ->assertForbidden();
});

it('lets an admin open the site analytics page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.analytics.index'))
        ->assertOk()
        ->assertSeeLivewire(SiteAnalytics::class);
});

it('renders analytics for an admin with the default range', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(SiteAnalytics::class)
        ->assertOk()
        ->assertSet('days', 7)
        ->set('days', 30)
        ->assertSet('days', 30);
});
