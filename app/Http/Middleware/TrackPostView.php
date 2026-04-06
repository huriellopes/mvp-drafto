<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TrackPostView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->routeIs('posts.show') && $response->getStatusCode() === 200) {
            $post = $request->route('slug');

            if ($post instanceof Post || $post = Post::where('slug', $post)->first()) {
                $this->logView($post, $request);
            }
        }

        return $response;
    }

    private function logView(Post $post, Request $request): void
    {
        $sessionId = session()->getId();

        $exists = $post->views()
            ->where(function ($q) use ($sessionId, $request) {
                $q->where('session_id', $sessionId)->orWhere('ip_hash', $request->ip());
            })
            ->where('viewed_at', '>', now()->subHour())
            ->exists();

        if (!$exists) {
            $post->views()->create([
                'user_id' => auth()->id(),
                'session_id' => $sessionId,
                'ip_hash' => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'viewed_at' => now(),
            ]);

            $post->increment('views_count');
        }
    }
}
