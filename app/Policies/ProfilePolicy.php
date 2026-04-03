<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN) ? true : null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Profile $profile): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isActive()
            && $user->profile === null;
    }

    public function update(User $user, Profile $profile): bool
    {
        return $user->isActive()
            && $profile->user_id === $user->id;
    }

    public function delete(User $user, Profile $profile): bool
    {
        return false;
    }
}
