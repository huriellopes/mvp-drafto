<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Masmerise\Toaster\Toaster;
use Symfony\Component\HttpFoundation\Response;

final class CheckModuleAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        $user = $request->user();

        // Sênior: Se o usuário não estiver logado ou o módulo não estiver disponível
        if (!$user || !$user->isModuleAvailable($moduleSlug)) {

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Este recurso não está disponível para sua conta.',
                ], 403);
            }

            Toaster::warning('Este recurso não está disponível para sua conta.');

            return redirect()->route('dashboard.index');
        }

        return $next($request);
    }
}
