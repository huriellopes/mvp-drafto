<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\ShortLinkFilterData;
use App\Models\ShortLink;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListShortLinksAction
{
    /**
     * Sênior: Lista links curtos com filtros e carregamento antecipado de relações polimórficas.
     */
    public function exec(ShortLinkFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return ShortLink::query()
            ->with(['user.profile', 'shortable'])
            ->when($filters->search, function (Builder $query, string $search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($perPage);
    }
}
