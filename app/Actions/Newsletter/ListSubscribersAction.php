<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\DTOs\NewsletterFilterData;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListSubscribersAction
{
    public function exec(NewsletterFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return NewsletterSubscriber::query()
            ->with('categories')
            ->when($filters->search, function (Builder $query, string $search) {
                $query->where('email', 'like', "%{$search}%");
            })
            ->when($filters->category_id, function (Builder $query, int $categoryId) {
                $query->whereHas('categories', fn ($q) => $q->where('post_categories.id', $categoryId));
            })
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($perPage);
    }
}
