<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Modules;

use App\Actions\Modules\UpdateModuleSettingAction;
use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\User;

beforeEach(function () {
    $this->action = app(UpdateModuleSettingAction::class);
});

it('initializes settings from an empty array when the pivot settings are null', function () {
    $user = User::factory()->create();
    $module = Module::where('slug', ModuleEnum::LINK_SHORTENER->value)->firstOrFail();

    // Attach with NULL settings so the json_decode branch is skipped and the
    // null-coalesce to [] (line 24) is exercised.
    $user->modules()->syncWithoutDetaching([
        $module->id => ['is_enabled' => true, 'settings' => null],
    ]);

    $this->action->exec($user->fresh(), ModuleEnum::LINK_SHORTENER, 'enable_for_posts', false);

    $pivot = $user->fresh()->modules()->where('slug', ModuleEnum::LINK_SHORTENER->value)->first()->pivot;
    $settings = is_string($pivot->settings) ? json_decode($pivot->settings, true) : $pivot->settings;

    expect($settings['enable_for_posts'])->toBeFalse();
});
