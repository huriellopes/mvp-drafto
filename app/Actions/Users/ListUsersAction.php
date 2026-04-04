<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListUsersAction
{
    /**
     * @param  array{search?: string, role?: string, status?: string, per_page?: int}  $filters
     */
    public function exec(array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->with(['profile'])
            ->whereNot('id', auth()->id())
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"),
                );
            })
            ->when($filters['role'] ?? null, fn ($q, $role) => $q->where('role', $role))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }
}
