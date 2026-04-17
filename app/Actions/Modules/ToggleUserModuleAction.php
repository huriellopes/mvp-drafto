<?php

declare(strict_types=1);

namespace App\Actions\Modules;

use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class ToggleUserModuleAction
{
    /**
     * Toggles the enabled status of a module for a specific user.
     */
    public function exec(User $user, Module $module): bool
    {
        $moduleInCollection = $user->modules
            ->firstWhere('id', $module->id);

        $currentStatus = $moduleInCollection
            ? $moduleInCollection->pivot->is_enabled
            : false;

        $newStatus = !$currentStatus;

        $user->modules()->updateExistingPivot($module->id, [
            'is_enabled' => $newStatus,
            'updated_at' => Carbon::now(),
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
