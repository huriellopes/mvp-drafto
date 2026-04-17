<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PostCategory;
use App\Models\User;
use App\Enums\RoleEnum;

class PostCategoryPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN) ? true : null;
    }

    public function update(User $user, PostCategory $postCategory): bool
    {
        // Só pode atualizar se for dono da categoria (não global)
        return $user->isActive() 
            && $postCategory->user_id !== null 
            && $postCategory->user_id === $user->id;
    }

    public function delete(User $user, PostCategory $postCategory): bool
    {
        return $user->isActive() 
            && $postCategory->user_id !== null 
            && $postCategory->user_id === $user->id;
    }
}
