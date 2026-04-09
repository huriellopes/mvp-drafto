<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use App\DTOs\PostFiltersData;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPostsAction
{
    public function exec(PostFiltersData $filters): LengthAwarePaginator
    {
        return Post::query()
            ->where('user_id', auth()->id())
            ->with(['category', 'author.profile'])
            ->when($filters->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($filters->status, fn ($q) => $q->where('status', $filters->status))
            ->when($filters->notStatus, fn ($q) => $q->where('status', '!=', $filters->notStatus))
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($filters->perPage);
    }
}
