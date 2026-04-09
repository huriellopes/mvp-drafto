<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Collection;

final class GetSuggestedWritersAction
{
    public function exec(User $user, int $limit = 3): Collection
    {
        // 1. Identifica as categorias dos posts que o usuário interagiu
        $favoriteCategoryIds = PostCategory::whereHas('posts', function ($q) use ($user) {
            $q->whereHas('comments', fn ($c) => $c->where('user_id', $user->id))
                ->orWhereHas('likedByUsers', fn ($l) => $l->where('user_id', $user->id));
        })->pluck('id');

        // 2. Busca escritores que publicam nessas categorias e que o usuário NÃO segue
        return User::query()
            ->with(['profile'])
            ->where('id', '!=', $user->id)
            ->whereHas('posts', fn ($q) => $q->whereIn('category_id', $favoriteCategoryIds))
            ->whereDoesntHave('followers', fn ($q) => $q->where('follower_id', $user->id))
            ->withCount('publishedPosts')
            ->orderByDesc('published_posts_count')
            ->take($limit)
            ->get();
    }
}
