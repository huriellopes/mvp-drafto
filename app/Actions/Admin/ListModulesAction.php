<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\ModuleFilterData;
use App\Models\Module;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListModulesAction
{
    /**
     * List all modules with filtering and pagination.
     */
    public function exec(ModuleFilterData $dto): LengthAwarePaginator
    {
        return Module::query()
            ->when($dto->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy($dto->sortField, $dto->sortDirection)
            ->paginate($dto->perPage);
    }
}
