<?php

declare(strict_types=1);

namespace App\Actions\Comments;

use App\DTOs\CommentFilterData;
use App\Models\{Comment, User};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListCommentsAction
{
    public function exec(User $user, CommentFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Comment::query()
            ->with(['post:id,title,slug', 'author.profile', 'replies.author.profile']);

        if (! $user->isAdmin()) {
            $query->where(function (Builder $q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHas('parent', fn($parent) => $parent->where('user_id', $user->id));
            });
        }

        return $query
            ->when($filters->search, function ($q, $search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('post', fn($post) => $post->where('title', 'like', "%{$search}%"));
            })
            ->when($filters->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($perPage);
    }
}
