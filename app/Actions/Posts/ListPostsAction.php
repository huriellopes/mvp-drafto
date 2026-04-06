<?php

namespace App\Actions\Posts;

use App\DTOs\PostFiltersDTO;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPostsAction
{
    public function exec(PostFiltersDTO $filters): LengthAwarePaginator
    {
        $query = Post::query()
            ->with('category')
            ->where('user_id', auth()->id());

        if ($filters->search) {
            $query->where('title', 'like', "%{$filters->search}%");
        }

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->notStatus) {
            $query->whereNot('status', $filters->notStatus);
        }

        return $query
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($filters->perPage);
    }
}
