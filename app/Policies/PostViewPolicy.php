<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class PostViewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }
}
