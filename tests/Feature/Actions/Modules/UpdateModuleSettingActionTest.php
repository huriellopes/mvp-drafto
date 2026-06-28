<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Modules;

use App\Actions\Modules\UpdateModuleSettingAction;
use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\User;

beforeEach(function () {
    $this->action = app(UpdateModuleSettingAction::class);
    $this->module = Module::where('slug', ModuleEnum::LINK_SHORTENER->value)->firstOrFail();
});

it('sets a setting key on a module the user has', function () {
    $user = User::factory()->create();
    $user->modules()->syncWithoutDetaching([
        $this->module->id => ['is_enabled' => true, 'settings' => json_encode([])],
    ]);

    $this->action->exec($user, ModuleEnum::LINK_SHORTENER, 'enable_for_posts', false);

    $settings = $user->fresh()->modules()->where('slug', ModuleEnum::LINK_SHORTENER->value)->first()->pivot->settings;

    if (is_string($settings)) {
        $settings = json_decode($settings, true);
    }

    expect($settings['enable_for_posts'])->toBeFalse();
});

it('merges into existing settings without dropping previous keys', function () {
    $user = User::factory()->create();
    $user->modules()->syncWithoutDetaching([
        $this->module->id => ['is_enabled' => true, 'settings' => json_encode(['enable_for_profile' => true])],
    ]);

    $this->action->exec($user, ModuleEnum::LINK_SHORTENER, 'enable_for_posts', false);

    $settings = $user->fresh()->modules()->where('slug', ModuleEnum::LINK_SHORTENER->value)->first()->pivot->settings;

    if (is_string($settings)) {
        $settings = json_decode($settings, true);
    }

    expect($settings)->toMatchArray([
        'enable_for_profile' => true,
        'enable_for_posts' => false,
    ]);
});

it('is a no-op when the user does not have the module', function () {
    $user = User::factory()->create();
    $user->modules()->detach($this->module->id);

    $this->action->exec($user, ModuleEnum::LINK_SHORTENER, 'enable_for_posts', false);

    expect($user->fresh()->modules()->where('slug', ModuleEnum::LINK_SHORTENER->value)->exists())->toBeFalse();
});
