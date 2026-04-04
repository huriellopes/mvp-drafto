<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class StoreUserAction
{
    /**
     * @param  array{name: string, email: string, password: string, role: string, status: string}  $data
     *
     * @throws Throwable
     */
    public function exec(array $data): User
    {
        return DB::transaction(function () use ($data) {
            /** @var User $user */
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
                'status' => $data['status'] ?? UserStatusEnum::ACTIVE->value,
            ]);

            $user->profile()->create([
                'username' => Str::slug($user->name) . '-' . Str::lower(Str::random(4)),
            ]);

            return $user;
        });
    }
}
