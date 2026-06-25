<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

final class EnforceSingleSession
{
    public function handle(Login $event): void
    {
        $userId = $event->user->getAuthIdentifier();

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
