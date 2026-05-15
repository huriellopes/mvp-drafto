<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\DTOs\Public\PostFilterData;
use App\Enums\PostVisibilityEnum;
use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPublicPostsAction
{
    public function exec(PostFilterData $filters): LengthAwarePaginator
    {
        $query = Post::query()
            ->with(['author' => fn ($q) => $q->withFollowStatus(), 'category', 'tags'])
            ->published();

        // Filtro de Visibilidade Sênior (Lógica unificada)
        $query->where(function (Builder $q) {
            $q->where('visibility', PostVisibilityEnum::PUBLIC)
                ->orWhere(function (Builder $sub) {
                    $sub->where('visibility', PostVisibilityEnum::FOLLOWERS_ONLY);

                    if (auth()->guest()) {
                        $sub->whereRaw('1 = 0');

                        return;
                    }

                    $user = auth()->user();

                    if ($user->isAdmin()) {
                        return;
                    }

                    $sub->where(fn ($inner) => $inner->whereHas('author.followers', fn ($f) => $f->where('follower_id', $user->id))
                        ->orWhere('user_id', $user->id),
                    );
                });
        });

        // Busca Performática (Full-text)
        if ($filters->search) {
            $term = $filters->search;

            // Se o termo for curto (ex: < 3 chars), fulltext pode não funcionar dependendo do ft_min_word_len
            if (mb_strlen($term) <= 3) {
                $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%"),
                );
            } else {
                $query->whereFullText(['title', 'excerpt', 'content'], $term);
            }
        }

        if ($filters->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters->category));
        }

        if ($filters->tag) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $filters->tag));
        }

        if ($filters->type) {
            $query->where('type', $filters->type);
        }

        // Ordenação
        match ($filters->sort) {
            'popular' => $query->orderBy('views_count', 'desc'),
            'commented' => $query->orderBy('comments_count', 'desc'),
            default => $query->latest(),
        };

        return $query->paginate($filters->perPage);
    }
}
