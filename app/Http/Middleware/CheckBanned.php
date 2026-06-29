<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Response;

final class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->banned_until && now()->lessThan(auth()->user()->banned_until)) {
            $user = auth()->user();
            $banned_days = Date::now()->diffInDays($user->banned_until);
            $reason = $user->ban_reason;

            auth()->logout();

            return to_route('login')->withErrors([
                'email' => "Sua conta está suspensa por mais {$banned_days} dias. Motivo: {$reason}",
            ]);
        }

        return $next($request);
    }
}
