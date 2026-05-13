<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ModuleEnum;
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
        if (!$user->isActive() || $profile->user_id !== $user->id) {
            return false;
        }

        // Criterioso: Validar se ele está tentando mudar cores sem ter o plano Plus/Pro
        // Isso impede injeção de dados via request
        if (request()->has('primary_color') || request()->has('accent_color')) {
            return $user->getModuleSetting(ModuleEnum::PROFILE, 'allow_custom_colors', false);
        }

        return true;
    }

    public function delete(User $user, Profile $profile): bool
    {
        return false;
    }
}
