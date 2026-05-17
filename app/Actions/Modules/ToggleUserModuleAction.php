<?php

declare(strict_types=1);

namespace App\Actions\Modules;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class ToggleUserModuleAction
{
    public function exec(User $user, Module $module): bool
    {
        $pivot = $user->modules()->where('module_id', $module->id)->first()?->pivot;

        $newStatus = $pivot ? !$pivot->is_enabled : true;

        $user->modules()->syncWithoutDetaching([
            $module->id => [
                'is_enabled' => $newStatus,
                'updated_at' => Carbon::now(),
            ],
        ]);

        $this->invalidateCache($user, $module);

        return $newStatus;
    }

    private function invalidateCache(User $user, Module $module): void
    {
        Cache::forget("user_{$user->id}_module_{$module->slug->value}");
        Cache::forget("user_{$user->id}_active_modules");
    }
}
