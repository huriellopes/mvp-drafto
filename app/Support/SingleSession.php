<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Driver-agnostic "one active session per user" policy.
 *
 * Each login issues a random token that is stored both in the user's
 * session and in the cache. A session whose token no longer matches the
 * cached one was superseded by a newer login and must be terminated.
 * Unlike deleting rows from the `sessions` table, this works for any
 * session driver (redis, database, file).
 */
final class SingleSession
{
    public const string SESSION_KEY = 'auth.single_session_token';

    public static function issue(int|string $userId): string
    {
        $token = Str::random(40);

        Cache::forever(self::cacheKey($userId), $token);
        session()->put(self::SESSION_KEY, $token);

        return $token;
    }

    public static function current(int|string $userId): ?string
    {
        $token = Cache::get(self::cacheKey($userId));

        return is_string($token) ? $token : null;
    }

    public static function cacheKey(int|string $userId): string
    {
        return "auth:single-session:{$userId}";
    }
}
