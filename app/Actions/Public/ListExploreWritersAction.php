<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\DTOs\Public\ExploreWritersFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

final class ListExploreWritersAction
{
    public function exec(ExploreWritersFilterData $data): LengthAwarePaginator
    {
        $cacheKey = "explore_writers_p{$data->perPage}_s{$data->search}";

        return Cache::tags(['writers', 'explore'])
            ->remember($cacheKey, now()->addMinutes(30), function () use ($data) {
                return User::query()
                    ->with(['profile'])
                    ->withFollowStatus()
                    ->whereHas('profile')
                    ->when($data->search, function ($q) use ($data) {
                        $q->where('name', 'like', "%{$data->search}%")
                            ->orWhereHas('profile', fn ($p) => $p->where('username', 'like', "%{$data->search}%"));
                    })
                    ->withCount(['publishedPosts', 'followers'])
                    ->orderBy('published_posts_count', 'desc')
                    ->paginate($data->perPage);
            });
    }
}
