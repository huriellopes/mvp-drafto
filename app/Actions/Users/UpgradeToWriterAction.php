<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Traits\GeneratesUsername;
use Illuminate\Support\Facades\DB;
use Throwable;

final class UpgradeToWriterAction
{
    use GeneratesUsername;

    /**
     * @throws Throwable
     */
    public function exec(User $user): void
    {
        if ($user->role !== RoleEnum::READER) {
            return;
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'role' => RoleEnum::WRITER,
            ]);

            if (!$user->profile()->exists()) {
                $user->profile()->create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $this->generateUniqueUsername($user->name),
                    'is_verified' => true,
                ]);
            } else {
                $user->profile()->update([
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            }
        });
    }
}
