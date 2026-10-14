<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Support\SingleSession;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

final class EnforceSingleSession
{
    /**
     * Issues a new single-session token on every login. Sessions holding an
     * older token are terminated by the EnforceSingleSession middleware,
     * regardless of the session driver (redis, database, file).
     */
    public function handle(Login $event): void
    {
        $userId = $event->user->getAuthIdentifier();

        SingleSession::issue($userId);

        // With the database driver, also prune the stale rows (housekeeping;
        // the token above is what enforces the policy for every driver).
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $userId)
                ->where('id', '!=', Session::getId())
                ->delete();
        }

        if (!$event->remember && method_exists($event->user, 'setRememberToken')) {
            $event->user->setRememberToken(Str::random(60));
            $event->user->save();
        }
    }
}
