<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Renderiza a tela 500 branded (com o código do erro / trace_id) em produção.
 * Em modo debug, devolve null para deixar o handler padrão (Ignition) atuar.
 * As demais telas (4xx) usam as views errors/{code} padrão do Laravel.
 */
final class BrandedErrorRenderer
{
    public function render(Throwable $e, Request $request): ?Response
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($e instanceof AuthenticationException || $e instanceof ValidationException) {
            return null;
        }

        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        if ($status >= 500 && !config('app.debug')) {
            return response()->view('errors.500', [
                'traceId' => $request->hasSession() ? $request->session()->get('trace_id') : null,
            ], 500);
        }

        return null;
    }
}
