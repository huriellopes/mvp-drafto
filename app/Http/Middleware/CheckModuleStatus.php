<?php

namespace App\Http\Middleware;

use App\Enums\ModuleEnum;
use App\Enums\RoleEnum;
use Closure;
use App\Models\Module;
use Illuminate\Http\Request;

class CheckModuleStatus
{
    public function handle(Request $request, Closure $next, string $slug)
    {
        if (auth()->check() && auth()->user()->hasRole(RoleEnum::SUPER_ADMIN)) {
            return $next($request);
        }

        $module = ModuleEnum::tryFrom($slug);

        if (!$module || !Module::isEnabled($module)) {
            abort(404, 'Funcionalidade temporariamente indisponível.');
        }

        return $next($request);
    }
}
