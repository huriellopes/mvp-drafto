<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\UpdateUserData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final class UpdateUserAction
{
    /**
     * @throws Throwable
     */
    public function exec(User $user, UpdateUserData $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            if ($data->password && filled($data->password)) {
                $user->password = $data->password;
            }

            return $user->fill(collect($data->toArray())->except('password')->filter()->toArray())->save();
        });
    }
}
