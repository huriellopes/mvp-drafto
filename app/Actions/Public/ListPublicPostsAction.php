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
        return Post::query()
            ->with(['author.profile', 'category', 'tags'])
            ->published()
            ->where(function (Builder $query) {
                $query->where('visibility', PostVisibilityEnum::PUBLIC)
                    ->orWhere(function (Builder $q) {
                        $q->where('visibility', PostVisibilityEnum::FOLLOWERS_ONLY);

                        if (auth()->guest()) {
                            $q->whereRaw('1 = 0');

                            return;
                        }

                        $user = auth()->user();

                        if ($user->isAdmin()) {
                            return;
                        }

                        $q->where(fn ($sub) => $sub->whereHas('author.followers', fn ($f) => $f->where('follower_id', $user->id))
                            ->orWhere('user_id', $user->id),
                        );
                    });
            })
            ->when($filters->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($filters->category, fn ($q, $c) => $q->whereHas('category', fn ($cat) => $cat->where('slug', $c)))
            ->when($filters->tag, fn ($q, $t) => $q->whereHas('tags', fn ($tag) => $tag->where('slug', $t)))
            ->when($filters->type, fn ($q, $type) => $q->where('type', $type))
            ->tap(fn ($q) => match ($filters->sort) {
                'popular' => $q->orderBy('views_count', 'desc'),
                'commented' => $q->orderBy('comments_count', 'desc'),
                default => $q->latest(),
            })
            ->paginate($filters->perPage);
    }
}
