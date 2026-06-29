<?php

declare(strict_types=1);

namespace App\Actions\Modules;

use App\Enums\ModuleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

final class UpdateModuleSettingAction
{
    public function exec(User $user, ModuleEnum $module, string $key, mixed $value): void
    {
        $userModule = $user->modules()->where('slug', $module->value)->first();

        if (!$userModule) {
            return;
        }

        $settings = is_string($userModule->pivot->settings)
            ? json_decode($userModule->pivot->settings, true)
            : ($userModule->pivot->settings ?? []);

        $settings[$key] = $value;

        $user->modules()->updateExistingPivot($userModule->id, [
            'settings' => $settings,
            'updated_at' => Date::now(),
        ]);

        Cache::forget("user_{$user->id}_module_{$module->value}");
    }
}
