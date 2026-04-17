<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Database\QueryException;
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

        $post = $request->route('slug');

        if (!($post instanceof Post)) {
            $post = Post::where('slug', $post)->first();
        }

        if ($post) {
            $this->logView($post, $request);
        }
    }

    private function logView(Post $post, Request $request): void
    {
        $sessionId = session()->getId();
        $ipHash = md5($request->ip());

        // Sênior: Verifica se já houve visualização recente (throttling de visualizações)
        $exists = $post->views()
            ->where(function ($q) use ($sessionId, $ipHash) {
                $q->where('session_id', $sessionId)->orWhere('ip_hash', $ipHash);
            })
            ->where('viewed_at', '>', now()->subHour())
            ->exists();

        if ($exists) {
            return;
        }

        try {
            $post->views()->create([
                'user_id' => auth()->id(),
                'session_id' => $sessionId,
                'ip_hash' => $ipHash,
                'user_agent' => mb_substr($request->userAgent() ?? '', 0, 255),
                'viewed_at' => now(),
            ]);

            $post->increment('views_count');
        } catch (QueryException $e) {
            // Silencia duplicate key race condition em alta concorrência
        }
    }
}
