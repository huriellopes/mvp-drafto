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
     * A writer can view stats of their own posts.
     */
    public function viewStats(User $user, Post $post): bool
    {
        return $post->user_id === $user->id;
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }
}
