<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class GetSuggestedWritersAction
{
    public function exec(User $user, int $limit = 3): Collection
    {
        // A descoberta (whereHas aninhados sobre categorias/comentários/curtidas)
        // é cara e não precisa ser recalculada a cada render do dashboard.
        // Cacheamos apenas os IDs (evita problemas de serialização de modelos) e
        // recarregamos os modelos leves por ID, preservando a ordem.
        $writerIds = Cache::remember(
            "suggested_writers_v1_{$user->id}_{$limit}",
            now()->addMinutes(15),
            fn (): array => $this->discoverWriterIds($user, $limit),
        );

        if ($writerIds === []) {
            return collect();
        }

        return User::query()
            ->with(['profile'])
            ->withCount('publishedPosts')
            ->whereIn('id', $writerIds)
            ->get()
            ->sortBy(fn (User $writer) => array_search($writer->id, $writerIds, true))
            ->values();
    }

    /**
     * Escritores que publicam nas categorias com que o usuário interagiu e que
     * ele ainda NÃO segue, ordenados pelo nº de publicações.
     *
     * @return array<int, int>
     */
    private function discoverWriterIds(User $user, int $limit): array
    {
        // 1. Categorias dos posts que o usuário comentou/curtiu
        $favoriteCategoryIds = PostCategory::whereHas('posts', function ($q) use ($user) {
            $q->whereHas('comments', fn ($c) => $c->where('user_id', $user->id))
                ->orWhereHas('likedByUsers', fn ($l) => $l->where('user_id', $user->id));
        })->pluck('id');

        // 2. Escritores dessas categorias que o usuário NÃO segue
        return User::query()
            ->where('id', '!=', $user->id)
            ->whereHas('posts', fn ($q) => $q->whereIn('category_id', $favoriteCategoryIds))
            ->whereDoesntHave('followers', fn ($q) => $q->where('follower_id', $user->id))
            ->withCount('publishedPosts')
            ->orderByDesc('published_posts_count')
            ->take($limit)
            ->pluck('id')
            ->all();
    }
}
