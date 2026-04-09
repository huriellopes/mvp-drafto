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
        if (auth()->check() && auth()->user()->hasRole(RoleEnum::SUPER_ADMIN)) {
            return true;
        }

        $module = $slug instanceof ModuleEnum
            ? $slug
            : ModuleEnum::tryFrom($slug);

        if (!$module) {
            return false;
        }

        return Module::isEnabled($module);
    }
}
