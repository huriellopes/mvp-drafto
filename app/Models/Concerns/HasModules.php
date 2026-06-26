<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\ModuleEnum;
use App\Enums\RoleEnum;
use App\Models\Module;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Acesso a módulos da plataforma e ao "plano" do usuário.
 */
trait HasModules
{
    /**
     * Cache em tempo de execução para os módulos disponíveis do usuário.
     */
    protected ?Collection $loadedModules = null;

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class)
            ->withPivot('is_enabled', 'settings')
            ->withTimestamps();
    }

    public function isModuleAvailable(string|ModuleEnum $slug): bool
    {
        if ($slug instanceof ModuleEnum) {
            $slug = $slug->value;
        }

        if ($this->hasRole(RoleEnum::SUPER_ADMIN)) {
            return true;
        }

        // Sênior: Usamos cache em memória para evitar re-processamento da relação
        if ($this->loadedModules === null) {
            $this->loadedModules = $this->modules;
        }

        $module = $this->loadedModules->firstWhere('slug', $slug);

        if (!$module) {
            return false;
        }

        return $module->is_enabled && (bool) $module->pivot->is_enabled;
    }

    /**
     * Sênior: Retorna uma configuração específica de um módulo para o usuário.
     */
    public function getModuleSetting(string|ModuleEnum $module, string $key, mixed $default = null): mixed
    {
        if ($module instanceof ModuleEnum) {
            $module = $module->value;
        }

        if ($this->loadedModules === null) {
            $this->loadedModules = $this->modules;
        }

        $userModule = $this->loadedModules->firstWhere('slug', $module);

        if (!$userModule) {
            return $default;
        }

        $settings = is_string($userModule->pivot->settings)
            ? json_decode($userModule->pivot->settings, true)
            : $userModule->pivot->settings;

        return $settings[$key] ?? $default;
    }

    /**
     * Sênior: Como a plataforma é gratuita, o slug do plano é sempre 'free'.
     */
    public function getPlanSlug(): string
    {
        return 'free';
    }

    /**
     * Sênior: Nome amigável do plano (sempre Gratuito agora).
     */
    public function getPlanName(): string
    {
        return 'Gratuito';
    }
}
