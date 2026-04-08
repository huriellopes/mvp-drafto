<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\ModuleFilterDTO;
use App\Models\Module;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class ListModulesAction
{
    public function exec(ModuleFilterDTO $dto): LengthAwarePaginator
    {
        return Module::query()
            ->when($dto->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy($dto->sortField, $dto->sortDirection)
            ->paginate($dto->perPage);
    }
}
