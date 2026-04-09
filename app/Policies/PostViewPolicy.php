<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostViewPolicy
{
    /**
     * Super Admin sees everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * A writer can view stats of their own posts if they have a PRO subscription or Lifetime access.
     */
    public function viewStats(User $user, Post $post): bool
    {
        // Escritor deve ser dono do post e ter acesso premium
        return $post->user_id === $user->id && $user->hasPremiumAccess();
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }
}
