<?php

declare(strict_types=1);

use App\Enums\ModuleEnum;
use App\Enums\RoleEnum;
use App\Models\Module;

if (!function_exists('is_module_enabled')) {
    /**
     * Verifica se um módulo está ativo.
     * Retorna TRUE sempre se o usuário for Admin.
     */
    function is_module_enabled(ModuleEnum|string $slug): bool
    {
        $user = auth()->user();

        // Admin bypass
        if ($user && $user->hasRole(RoleEnum::SUPER_ADMIN)) {
            return true;
        }

        $moduleSlug = $slug instanceof ModuleEnum ? $slug->value : $slug;
        $moduleEnum = $slug instanceof ModuleEnum ? $slug : ModuleEnum::tryFrom((string) $slug);

        if (!$moduleEnum) {
            return false;
        }

        // 1. Verifica se o módulo existe e está ativo GLOBALMENTE
        if (!Module::isEnabled($moduleEnum)) {
            return false;
        }

        // 2. Se houver usuário logado, verifica a permissão específica (pivot)
        if ($user) {
            return $user->isModuleAvailable($moduleSlug);
        }

        return true;
    }
}
