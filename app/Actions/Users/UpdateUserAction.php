<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\UpdateUserData;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

final class UpdateUserAction
{
    /**
     * @throws Throwable
     */
    public function exec(User $user, UpdateUserData $data): bool
    {
        // Segurança: mesmo com UserIndex/UserForm exigindo 'admin' para
        // chegar até aqui, esta Action recusa promover alguém a
        // super_admin a menos que quem está executando já seja
        // super_admin — defesa em profundidade contra escalonamento de
        // privilégio caso esta Action seja chamada de outro lugar no
        // futuro (CLI, job, outra UI) sem a mesma proteção de rota/render.
        // `role` pode chegar como string ou RoleEnum (DTO aceita os dois).
        $requestedRole = $data->role instanceof RoleEnum
            ? $data->role
            : RoleEnum::tryFrom((string) $data->role);

        if ($requestedRole === RoleEnum::SUPER_ADMIN
            && !Auth::user()?->hasRole(RoleEnum::SUPER_ADMIN)) {
            return false;
        }

        return DB::transaction(function () use ($user, $data) {
            if ($data->password && filled($data->password)) {
                $user->password = $data->password;
            }

            return $user->fill(collect($data->toArray())->except('password')->filter()->toArray())->save();
        });
    }
}
