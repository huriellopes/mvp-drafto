<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Models\SiteView;
use Illuminate\Support\Facades\DB;

final class UpdateSiteViewDurationAction
{
    public function handle(string $sessionId, string $url, int $duration): void
    {
        // Update the most recent view for this session and URL
        // We use a limit to only update the latest one to avoid updating historical data
        $latestView = SiteView::where('session_id', $sessionId)
            ->where('url', $url)
            ->latest('viewed_at')
            ->first();

        if ($latestView) {
            $latestView->update([
                'duration' => $duration,
            ]);
        }
    }
}
