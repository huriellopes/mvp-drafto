<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Models\Post;
use App\Enums\PostVisibilityEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPublicPostsAction
{
    public function exec(array $filters = []): LengthAwarePaginator
    {
        return Post::query()
            ->with(['author.profile', 'category'])
            ->published()
            ->where(function (Builder $query) {
                $query->where('visibility', PostVisibilityEnum::PUBLIC)

                    ->orWhere(function (Builder $q) {
                        $q->where('visibility', PostVisibilityEnum::FOLLOWERS_ONLY)
                            ->where(function ($sub) {
                                if (auth()->check()) {
                                    $user = auth()->user();
                                    if ($user->isAdmin()) return;

                                    $sub->whereHas('author.followers', function ($f) use ($user) {
                                        $f->where('follower_id', $user->id);
                                    })
                                        ->orWhere('user_id', $user->id);
                                } else {
                                    $sub->whereRaw('1 = 0');
                                }
                            });
                    });
            })
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($filters['category'] ?? null, fn($q, $c) => $q->whereHas('category', fn($cat) => $cat->where('slug', $c)))
            ->latest()
            ->paginate(12);
    }
}
