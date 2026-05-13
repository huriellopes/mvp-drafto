<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class LogContextMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = (string) Str::uuid();

        // Armazena no container do Log para que todos os disparos automáticos do Laravel incluam o ID
        Log::shareContext([
            'trace_id' => $traceId,
            'user_id' => auth()->id(),
        ]);

        // Adiciona na sessão para uso pelo SystemLogger
        if ($request->hasSession()) {
            $request->session()->put('trace_id', $traceId);
        }

        $response = $next($request);

        // Adiciona o trace_id nos headers da resposta para facilitar debug pelo front ou suporte
        $response->headers->set('X-Trace-ID', $traceId);

        return $response;
    }
}
