<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Jobs\ProcessProfileViewJob;
use App\Models\User;
use App\Support\CookieConsent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TrackProfileView
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (!$request->routeIs('profile.show') || $response->getStatusCode() !== 200) {
            return;
        }

        // LGPD: só contabiliza a visualização (que armazena IP) com consentimento de análise.
        if (!CookieConsent::allows($request, 'analytics')) {
            return;
        }

        $username = $request->route('username');
        $username = mb_strtolower(str_replace('@', '', $username));

        // Busca o perfil de forma otimizada
        $profile = User::query()
            ->whereHas('profile', fn ($q) => $q->whereRaw('LOWER(username) = ?', [$username]))
            ->first()?->profile;

        if ($profile && (!auth()->check() || auth()->id() !== $profile->user_id)) {
            ProcessProfileViewJob::dispatch(
                $profile->id,
                auth()->id(),
                md5($request->ip() ?? 'unknown'),
            );
        }
    }
}
