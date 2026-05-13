<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final class UpdateUserAction
{
    /**
     * @param  array{name?: string, email?: string, password?: string, role?: string, status?: string}  $data
     *
     * @throws Throwable
     */
    public function exec(User $user, array $data): bool
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['password']) && filled($data['password'])) {
                $user->password = $data['password'];
            }

            return $user->fill(collect($data)->except('password')->toArray())->save();
        });
    }
}
