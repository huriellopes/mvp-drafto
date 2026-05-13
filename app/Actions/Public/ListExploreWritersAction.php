<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\DTOs\Public\ExploreWritersFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListExploreWritersAction
{
    public function exec(ExploreWritersFilterData $data): LengthAwarePaginator
    {
        return User::query()
            ->with(['profile'])
            ->whereHas('profile')
            ->when($data->search, function ($q) use ($data) {
                $q->where('name', 'like', "%{$data->search}%")
                    ->orWhereHas('profile', fn ($p) => $p->where('username', 'like', "%{$data->search}%"));
            })
            ->withCount(['publishedPosts', 'followers'])
            ->orderBy('published_posts_count', 'desc')
            ->paginate($data->perPage);
    }
}
