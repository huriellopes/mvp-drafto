<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use function is_module_enabled;

final class CheckModuleStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $slug): mixed
    {
        if (!is_module_enabled($slug)) {
            abort(404, 'Funcionalidade temporariamente indisponível.');
        }

        return $next($request);
    }
}
