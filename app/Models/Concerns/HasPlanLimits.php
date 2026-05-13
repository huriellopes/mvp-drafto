<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\ModuleEnum;
use App\Enums\RoleEnum;
use App\Models\Module;

trait HasPlanLimits
{
    /**
     * Retorna uma configuração específica do módulo baseada no plano do usuário.
     */
    public function getModuleSetting(ModuleEnum $module, string $key, mixed $default = null): mixed
    {
        // 1. Regra Master: Super Admin é sempre Pro/Vitalicio
        if ($this->hasRole(RoleEnum::SUPER_ADMIN)) {
            return $this->resolveLimitValue($module, $key, 'pro') ?? $default;
        }

        // 2. Determina o nível do plano baseado no Cashier
        $planLevel = match (true) {
            $this->subscribed('pro') => 'pro',
            $this->subscribed('plus') => 'plus',
            default => 'free',
        };

        return $this->resolveLimitValue($module, $key, $planLevel) ?? $default;
    }

    /**
     * Resolve o valor no JSON de settings do módulo.
     */
    private function resolveLimitValue(ModuleEnum $module, string $key, string $level): mixed
    {
        $moduleData = Module::query()
            ->where('slug', $module)
            ->first();

        if (!$moduleData || !isset($moduleData->settings[$key])) {
            return null;
        }

        $setting = $moduleData->settings[$key];

        // Se for um array de planos, retorna o valor do nível específico
        if (is_array($setting) && isset($setting[$level])) {
            return $setting[$level];
        }

        // Se não for array (configuração global), retorna o valor bruto
        return $setting;
    }
}
