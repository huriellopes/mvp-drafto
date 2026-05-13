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
    /**
     * Sênior: Lista itens salvos com tratamento rigoroso de ambiguidades e performance.
     */
    public function exec(User $user, SavedPostsFilterData $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = Post::query();

        if ($user->isAdmin()) {
            // No modo Admin/Auditoria, vemos todos os salvos de todos os usuários
            $query->select('posts.*', 'saved_posts.created_at as saved_at', 'saved_posts.collection_id')
                ->join('saved_posts', 'posts.id', '=', 'saved_posts.post_id');
        } else {
            // Usuário comum vê apenas os seus
            $query->select('posts.*', 'saved_posts.created_at as saved_at', 'saved_posts.collection_id')
                ->join('saved_posts', function ($join) use ($user) {
                    $join->on('posts.id', '=', 'saved_posts.post_id')
                        ->where('saved_posts.user_id', '=', $user->id);
                });
        }

        // Definimos a coluna de ordenação para evitar ambiguidade (posts.created_at vs saved_posts.created_at)
        $sortColumn = $filters->sort === 'created_at' ? 'saved_posts.created_at' : "posts.{$filters->sort}";

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
