<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Collection;
use App\Models\User;

class CollectionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Collection $collection): bool
    {
        // Se for pública, qualquer um logado vê. Se privada, só o dono.
        if ($collection->is_public) {
            return true;
        }

        return $user->id === $collection->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Collection $collection): bool
    {
        return $user->isActive() && $user->id === $collection->user_id;
    }

    public function delete(User $user, Collection $collection): bool
    {
        return $user->isActive() && $user->id === $collection->user_id;
    }
}
