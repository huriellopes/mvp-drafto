<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\CommentStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN) ? true : null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Comment $comment): bool
    {
        return $comment->status === CommentStatusEnum::VISIBLE;
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->isActive()
            && $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->isActive()
            && $comment->user_id === $user->id;
    }

    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Comment $comment): bool
    {
        return false;
    }

    public function like(User $user, Comment $comment): bool
    {
        return $user->isActive()
            && $comment->status === CommentStatusEnum::VISIBLE;
    }
}
