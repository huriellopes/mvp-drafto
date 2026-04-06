<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\UserFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

final class ListUsersAction
{
    public function exec(UserFilterData $filters): LengthAwarePaginator
    {
        return User::query()
            ->with(['profile'])
            ->when($filters->search, function (Builder $query, string $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"),
                );
            })
            ->when($filters->role, fn ($q, $role) => $q->where('role', $role))
            ->when($filters->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy($filters->sort, $filters->direction)
            ->paginate($filters->per_page);
    }
}
