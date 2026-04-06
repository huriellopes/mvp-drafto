<?php

declare(strict_types=1);

namespace App\Actions\Follows;

use App\DTOs\FollowersFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListFollowsAction
{
    public function exec(
        User $user,
        FollowersFilterData $filters,
        string $type = 'followers',
        int $perPage = 15
    ): LengthAwarePaginator {

        $query = $type === 'following' ? $user->following() : $user->followers();

        return $query
            ->with(['profile'])
            ->when($filters->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn($p) => $p->where('username', 'like', "%{$search}%"));
                });
            })
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($perPage);
    }
}
