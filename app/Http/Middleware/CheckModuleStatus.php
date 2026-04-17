<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ModuleEnum;
use App\Enums\RoleEnum;
use App\Models\Module;
use Closure;
use Illuminate\Http\Request;

final class CheckModuleStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $slug): mixed
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
