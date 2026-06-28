<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Modules;

use App\Actions\Modules\ToggleUserModuleAction;
use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\User;

beforeEach(function () {
    $this->action = app(ToggleUserModuleAction::class);
    $this->module = Module::where('slug', ModuleEnum::LINK_SHORTENER->value)->firstOrFail();
});

it('enables a module that the user did not have', function () {
    $user = User::factory()->create();
    $user->modules()->detach($this->module->id);

    $result = $this->action->exec($user, $this->module);

    expect($result)->toBeTrue();

    $pivot = $user->modules()->where('module_id', $this->module->id)->first()->pivot;
    expect((bool) $pivot->is_enabled)->toBeTrue();
});

it('disables a currently enabled module', function () {
    $user = User::factory()->create();
    $user->modules()->syncWithoutDetaching([
        $this->module->id => ['is_enabled' => true],
    ]);

    $result = $this->action->exec($user, $this->module);

    expect($result)->toBeFalse();
});

it('re-enables a previously disabled module', function () {
    $user = User::factory()->create();
    $user->modules()->syncWithoutDetaching([
        $this->module->id => ['is_enabled' => false],
    ]);

    $result = $this->action->exec($user, $this->module);

    expect($result)->toBeTrue();
});
