<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PostStatusEnum;
use App\Enums\PostVisibilityEnum;
use App\Enums\RoleEnum;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN) ? true : null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Post $post): bool
    {
        if (
            $post->status === PostStatusEnum::PUBLISHED &&
            $post->visibility === PostVisibilityEnum::PUBLIC
        ) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($post->user_id === $user->id) {
            return true;
        }

        if (
            $post->status === PostStatusEnum::PUBLISHED &&
            $post->visibility === PostVisibilityEnum::UNLISTED
        ) {
            return true;
        }

        if (
            $post->status === PostStatusEnum::PUBLISHED &&
            $post->visibility === PostVisibilityEnum::FOLLOWERS_ONLY &&
            $user->isFollowing($post->author)
        ) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isActive()
            && in_array($user->role, [RoleEnum::WRITER, RoleEnum::READER], true);
    }

    public function update(User $user, Post $post): bool
    {
        return $user->isActive()
            && $post->user_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isActive()
            && $post->user_id === $user->id;
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->isActive()
            && $post->user_id === $user->id;
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return false;
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->isActive()
            && $post->user_id === $user->id;
    }

    public function unpublish(User $user, Post $post): bool
    {
        return $user->isActive()
            && $post->user_id === $user->id;
    }

    public function feature(User $user, Post $post): bool
    {
        return false;
    }
}
