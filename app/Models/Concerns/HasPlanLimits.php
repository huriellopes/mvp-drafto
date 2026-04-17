<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\ModuleEnum;
use App\Enums\PostStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Module;

trait HasPlanLimits
{
    /**
     * Cache estático para evitar múltiplas queries de módulos no mesmo request.
     */
    protected static array $moduleCache = [];

    /**
     * Retorna uma configuração específica do módulo baseada no plano do usuário.
     */
    public function getModuleSetting(ModuleEnum $module, string $key, mixed $default = null): mixed
    {
        // 1. Regra Master: Super Admin é sempre Pro/Vitalicio
        if ($this->hasRole(RoleEnum::SUPER_ADMIN)) {
            return $this->resolveLimitValue($module, $key, PlanEnum::PRO->value) ?? $default;
        }

        // 2. Determina o nível do plano baseado no Cashier, lifetime ou trial
        $planLevel = match (true) {
            $this->is_lifetime => PlanEnum::PRO->value,
            $this->onTrial() => PlanEnum::PRO->value,
            $this->subscribed(PlanEnum::PRO->value) => PlanEnum::PRO->value,
            $this->subscribed(PlanEnum::PLUS->value) => PlanEnum::PLUS->value,
            default => PlanEnum::FREE->value,
        };

        return $this->resolveLimitValue($module, $key, $planLevel) ?? $default;
    }

    /**
     * Verifica se o usuário atingiu o limite de posts do seu plano.
     */
    public function hasReachedPostLimit(): bool
    {
        $limit = (int) $this->getModuleSetting(ModuleEnum::MY_POSTS, 'max_posts', 5);

        $count = $this->posts()
            ->where('status', PostStatusEnum::PUBLISHED)
            ->whereMonth('published_at', now()->month)
            ->count();

        return $count >= $limit;
    }

    /**
     * Verifica se o usuário atingiu o limite de rascunhos do seu plano.
     */
    public function hasReachedDraftLimit(): bool
    {
        $limit = (int) $this->getModuleSetting(ModuleEnum::DRAFT, 'max_drafts', 3);

        $count = $this->posts()
            ->where('status', PostStatusEnum::DRAFT)
            ->count();

        return $count >= $limit;
    }

    /**
     * Resolve o valor no JSON de settings do módulo.
     */
    public function resolveLimitValue(ModuleEnum $module, string $key, string $level): mixed
    {
        $moduleData = $this->getModuleData($module);

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

    /**
     * Retorna o nome amigável do plano atual.
     */
    public function getPlanName(): string
    {
        if ($this->hasRole(RoleEnum::SUPER_ADMIN)) {
            return 'Administrador';
        }

        if ($this->is_lifetime) {
            return PlanEnum::PRO->label() . ' (Vitalício)';
        }

        if ($this->onTrial()) {
            return PlanEnum::PRO->label() . ' (Degustação)';
        }

        if ($this->subscribed(PlanEnum::PRO->value)) {
            return PlanEnum::PRO->label();
        }

        if ($this->subscribed(PlanEnum::PLUS->value)) {
            return PlanEnum::PLUS->label();
        }

        return PlanEnum::FREE->label();
    }

    /**
     * Busca o módulo com cache em memória (Request-level cache).
     */
    private function getModuleData(ModuleEnum $module): ?Module
    {
        $slug = $module->value;

        if (!isset(static::$moduleCache[$slug])) {
            static::$moduleCache[$slug] = Module::query()
                ->where('slug', $slug)
                ->first();
        }

        return static::$moduleCache[$slug];
    }
}
