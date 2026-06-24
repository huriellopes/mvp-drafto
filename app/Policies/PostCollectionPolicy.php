<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\PostCollection;
use App\Models\User;

class PostCollectionPolicy
{
    /**
     * Super Admin pode tudo.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleEnum::WRITER);
    }

    public function view(User $user, PostCollection $collection): bool
    {
        return $collection->user_id === $user->id;
    }

    public function update(User $user, PostCollection $collection): bool
    {
        return $collection->user_id === $user->id;
    }

    public function delete(User $user, PostCollection $collection): bool
    {
        return $collection->user_id === $user->id;
    }
}
