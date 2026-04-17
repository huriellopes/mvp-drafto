<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Models\Module;
use App\Enums\PlanEnum;

class UserObserver
{
    /**
     * Executado após o usuário ser criado no banco.
     */
    public function created(User $user): void
    {
        $this->syncModules($user);
    }

    /**
     * Executado sempre que o usuário for atualizado.
     * Útil para quando o plano muda (upgrade/downgrade).
     */
    public function updated(User $user): void
    {
        // Só re-sincroniza se a coluna 'plan_id' ou 'role' mudou
        if ($user->isDirty(['plan_id', 'role'])) {
            $this->syncModules($user);
        }
    }

    /**
     * Lógica centralizada de provisionamento de módulos.
     */
    private function syncModules(User $user): void
    {
        $modules = Module::query()
            ->where('is_enabled', true)
            ->get();

        $syncData = [];

        $planSlug = $user->plan?->slug ?? PlanEnum::FREE->value;

        foreach ($modules as $module) {
            $moduleSlugString = $module->slug instanceof \UnitEnum ? $module->slug->value : $module->slug;

            $planConfig = config("plans.{$planSlug}.modules.{$moduleSlugString}", []);

            $globalSettings = $module->settings ?? [];

            $userSettings = array_map(function ($values) use ($planSlug) {
                return is_array($values) ? ($values[$planSlug] ?? $values) : $values;
            }, $globalSettings);

            $syncData[$module->id] = [
                'is_enabled' => $planConfig['enabled'] ?? true,
                'settings' => json_encode($userSettings),
            ];
        }

        $user->modules()->sync($syncData);
    }
}
