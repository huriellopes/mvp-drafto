<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Dashboard\Admin\Modules\ModuleIndex;
use App\Livewire\Forms\Admin\ModuleForm;
use App\Models\Module;
use App\Models\User;
use Livewire\Livewire;

it('toggles a module enabled state through the admin module index', function () {
    $admin = User::factory()->superAdmin()->create();
    $module = Module::query()->first();
    $module->update(['is_enabled' => true]);

    Livewire::actingAs($admin)
        ->test(ModuleIndex::class)
        ->call('toggleModule', $module)
        ->assertHasNoErrors();

    expect($module->fresh()->is_enabled)->toBeFalse();

    Livewire::actingAs($admin)
        ->test(ModuleIndex::class)
        ->call('toggleModule', $module->fresh());

    expect($module->fresh()->is_enabled)->toBeTrue();
});

it('hydrates the form state from a module via setModule', function () {
    $admin = User::factory()->superAdmin()->create();
    $module = Module::query()->first();
    $module->update(['is_enabled' => true]);

    $this->actingAs($admin);

    $form = new ModuleForm(new ModuleIndex(), 'form');
    $form->setModule($module);

    expect($form->is_enabled)->toBeTrue();

    $form->is_enabled = false;
    $form->update();

    expect($module->fresh()->is_enabled)->toBeFalse();
});
