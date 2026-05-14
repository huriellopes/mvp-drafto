<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\User;

class FollowerPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function canHaveFollowers(User $target): bool
    {
        // Validamos se o módulo de seguidores está ativo no sistema.
        return true;
    }

    public function delete(User $user, User $target): bool
    {
        // Só pode "deletar" (unfollow) se for o próprio seguidor
        return $user->isActive();
    }
}
