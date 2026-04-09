<?php

declare(strict_types=1);

namespace App\Actions\PostViews;

use App\DTOs\PostViewFilterData;
use App\Models\PostView;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPostViewsAction
{
    public function exec(PostViewFilterData $filters, int $perPage = 20): LengthAwarePaginator
    {
        return PostView::query()
            ->with(['post:id,title', 'user:id,name'])
            ->when($filters->search, function ($q, $search) {
                $q->whereHas('post', fn ($p) => $p->where('title', 'like', "%{$search}%"))
                    ->orWhere('ip_hash', 'like', "%{$search}%");
            })
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($perPage);
    }
}
