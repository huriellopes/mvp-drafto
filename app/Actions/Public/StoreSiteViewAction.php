<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\DTOs\Public\StoreSiteViewData;
use App\Models\SiteView;

final class StoreSiteViewAction
{
    public function handle(StoreSiteViewData $data): SiteView
    {
        return SiteView::create([
            'user_id' => $data->userId,
            'url' => $data->url,
            'ip_address' => $data->ipAddress,
            'user_agent' => mb_substr((string) $data->userAgent, 0, 1000),
            'session_id' => $data->sessionId,
            'search_query' => $data->searchQuery,
            'duration' => $data->duration,
            'viewed_at' => now(),
        ]);
    }
}
