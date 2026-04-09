<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

final class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->banned_until && now()->lessThan(auth()->user()->banned_until)) {
            $banned_days = Carbon::now()->diffInDays(auth()->user()->banned_until);
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => "Sua conta está suspensa por mais {$banned_days} dias. Motivo: " . auth()->user()->ban_reason,
            ]);
        }

        return $next($request);
    }
}
