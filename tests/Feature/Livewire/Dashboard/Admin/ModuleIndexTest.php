<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Livewire\Dashboard\Admin\Modules\ModuleIndex;
use App\Models\Module;
use App\Models\User;
use Livewire\Livewire;

it('blocks non-admins from the modules page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.modules.index'))
        ->assertForbidden();
});

it('lets an admin open the modules page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.modules.index'))
        ->assertOk()
        ->assertSeeLivewire(ModuleIndex::class);
});

it('lists modules and filters by search', function () {
    $admin = User::factory()->superAdmin()->create();
    $module = Module::query()->first();

    Livewire::actingAs($admin)
        ->test(ModuleIndex::class)
        ->call('$refresh')
        ->assertSee($module->name)
        ->set('search', $module->name)
        ->assertSee($module->name);
});

it('resets the page when the search or per-page changes', function () {
    $admin = User::factory()->superAdmin()->create();

    Livewire::actingAs($admin)
        ->test(ModuleIndex::class)
        ->set('perPage', 'all')
        ->assertOk()
        ->set('search', 'x')
        ->assertOk();
});
