<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Jobs\ProcessPostViewJob;
use App\Models\Post;
use App\Support\CookieConsent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TrackPostView
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     * Sênior: Performance - Processamento pesado fora do ciclo de vida da request.
     */
    public function terminate(Request $request, Response $response): void
    {
        if (!$request->routeIs('posts.show') || $response->getStatusCode() !== 200) {
            return;
        }

        // LGPD: só contabiliza a visualização (que armazena IP/UA) com consentimento de análise.
        if (!CookieConsent::allows($request, 'analytics')) {
            return;
        }

        $post = $request->route('slug');

        if (!($post instanceof Post)) {
            $post = Post::where('slug', $post)->first();
        }

        // O autor não contabiliza views no próprio post.
        if ($post && auth()->id() === $post->user_id) {
            return;
        }

        if ($post) {
            dispatch(new ProcessPostViewJob($post->id, auth()->id(), session()->getId(), md5($request->ip() ?? 'unknown'), $request->userAgent() ?? 'unknown'));
        }
    }
}
