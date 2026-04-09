<?php

declare(strict_types=1);

namespace App\Actions\Saved;

use App\DTOs\SavedPostsFilterData;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListSavedPostsAction
{
    public function exec(User $user, SavedPostsFilterData $filters, int $perPage = 12): LengthAwarePaginator
    {
        if ($user->isAdmin()) {
            $query = Post::query()
                ->select('posts.*', 'saved_posts.created_at as saved_at')
                ->join('saved_posts', 'posts.id', '=', 'saved_posts.post_id');

            $sortColumn = $filters->sort === 'created_at' ? 'saved_posts.created_at' : "posts.{$filters->sort}";
        } else {
            $query = $user->savedPosts();
            $sortColumn = $filters->sort === 'created_at' ? 'saved_posts.created_at' : $filters->sort;
        }

        return $query->with(['author.profile', 'category'])
            ->published()
            ->public()
            ->when($filters->search, function (Builder $q, $search) {
                $q->where('posts.title', 'like', "%{$search}%");
            })
            ->when($filters->categoryId, function (Builder $q, $catId) {
                $q->where('posts.category_id', $catId);
            })
            ->when($filters->collectionId, function (Builder $q, $collId) {
                $q->where('saved_posts.collection_id', $collId);
            })
            ->orderBy($sortColumn, $filters->direction)
            ->paginate($perPage);
    }
}
