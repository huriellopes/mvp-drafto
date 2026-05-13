<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUsernameHasAtPrefix
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        if (str_starts_with($path, '@')) {
            return $next($request);
        }

        $username = $request->segment(1);

        if ($username) {
            $exists = Profile::where('username', $username)->exists();

            if ($exists) {
                return redirect()->to('/@' . $username, 301);
            }
        }

        return $next($request);
    }
}
