<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\SingleSession;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Terminates sessions superseded by a newer login of the same user.
 *
 * Sessions without a cached token (e.g. issued before this policy existed
 * or after a cache flush) are left alone; they are terminated as soon as
 * the user logs in somewhere else.
 */
final class EnforceSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || !$request->hasSession()) {
            return $next($request);
        }

        $expected = SingleSession::current($user->getAuthIdentifier());

        if ($expected !== null && $request->session()->get(SingleSession::SESSION_KEY) !== $expected) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw new AuthenticationException('Unauthenticated.', ['web'], route('login'));
        }

        return $next($request);
    }
}
