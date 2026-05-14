<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckEmailVerificationInterval
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->hasVerifiedEmail() && $user->hasVerificationExpired()) {
            return redirect()->route('verification.notice')
                ->with('error', 'Seu prazo de 15 dias para verificação expirou.');
        }

        return $next($request);
    }
}
