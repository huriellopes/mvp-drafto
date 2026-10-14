<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\DB;
use SessionHandlerInterface;

/**
 * Copies still-valid sessions from the `sessions` table into another session
 * handler (e.g. Redis), keeping the same session ids, so switching
 * SESSION_DRIVER does not log every user out.
 *
 * Idempotent: running it again just overwrites the copies with fresh data.
 */
final class CopyDatabaseSessionsAction
{
    public function exec(SessionHandlerInterface $target, int $lifetimeMinutes, bool $dryRun = false): int
    {
        $copied = 0;

        DB::table((string) config('session.table', 'sessions'))
            ->where('last_activity', '>=', now()->subMinutes($lifetimeMinutes)->getTimestamp())
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($target, $dryRun, &$copied): void {
                foreach ($rows as $row) {
                    // DatabaseSessionHandler stores the payload base64-encoded.
                    $payload = base64_decode((string) $row->payload, true);

                    if ($payload === false) {
                        continue;
                    }

                    if (!$dryRun) {
                        $target->write((string) $row->id, $payload);
                    }

                    $copied++;
                }
            });

        return $copied;
    }
}
