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
        return $user->hasRole(RoleEnum::WRITER);
    }

    public function delete(User $user, User $target): bool
    {
        // Só pode "deletar" (unfollow) se for o próprio seguidor
        return $user->isActive();
    }
}
