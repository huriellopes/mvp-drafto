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
    /**
     * Super Admin has lifetime access to everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
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

        // Admin e Autor veem o post
        if ($user->isAdmin() || $post->user_id === $user->id) {
            return true;
        }

        if (
            $post->status === PostStatusEnum::PUBLISHED &&
            in_array($post->visibility, [PostVisibilityEnum::UNLISTED, PostVisibilityEnum::PREMIUM], true)
        ) {
            return true;
        }

        if (
            $post->status === PostStatusEnum::PUBLISHED &&
            $post->visibility === PostVisibilityEnum::FOLLOWERS_ONLY &&
            $post->author instanceof User &&
            $user->isFollowing($post->author)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can view the actual content of the post.
     * Handles Paywall and Lifetime logic.
     */
    public function viewContent(?User $user, Post $post): bool
    {
        if ($post->visibility !== PostVisibilityEnum::PREMIUM) {
            return $this->view($user, $post);
        }

        if (!$user) {
            return false;
        }

        // Autor vê seu próprio conteúdo
        if ($post->user_id === $user->id) {
            return true;
        }

        // Abstração centralizada no Model User
        return $user->hasPremiumAccess();
    }

    public function create(User $user): bool
    {
        return $user->isActive()
            && in_array($user->role, [RoleEnum::WRITER, RoleEnum::READER, RoleEnum::SUPER_ADMIN], true);
    }

    public function update(User $user, Post $post): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $post->user_id === $user->id);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $post->user_id === $user->id);
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $post->user_id === $user->id);
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $post->user_id === $user->id);
    }

    public function unpublish(User $user, Post $post): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $post->user_id === $user->id);
    }

    public function feature(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }
}
