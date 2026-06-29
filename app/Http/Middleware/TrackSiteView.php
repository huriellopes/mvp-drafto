<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\DTOs\Public\StoreSiteViewData;
use App\Jobs\ProcessSiteViewJob;
use App\Support\CookieConsent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TrackSiteView
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // Only track GET requests that are successful
        if (!$request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return;
        }

        // Exclude specific paths or routes if needed (e.g., telescope, livewire internal)
        if ($request->is('livewire/*', 'up', 'horizon/*', 'telescope/*', 'admin/*', 'analytics/*')) {
            return;
        }

        // LGPD: só rastreia visitas (que armazenam IP) com consentimento de análise.
        if (!CookieConsent::allows($request, 'analytics')) {
            return;
        }

        $searchQuery = $request->query('search') ?? $request->query('q');

        $data = new StoreSiteViewData(
            userId: auth()->id(),
            url: $request->fullUrl(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            sessionId: session()->getId(),
            searchQuery: (string) $searchQuery ?: null,
            duration: 0,
        );

        dispatch(new ProcessSiteViewJob($data));
    }
}
