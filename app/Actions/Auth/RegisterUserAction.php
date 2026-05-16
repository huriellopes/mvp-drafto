<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\RegisterUserData;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Notifications\Auth\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class RegisterUserAction
{
    /**
     * @throws Throwable
     */
    public function exec(RegisterUserData $data): User
    {
        return DB::transaction(function () use ($data) {
            $role = $this->validateRole($data->role);
            $isWriter = ($role === RoleEnum::WRITER->value);

            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
                'role' => $role,
                'status' => UserStatusEnum::ACTIVE,
                'ip_address' => request()->ip(),
                'last_login_at' => Carbon::now(),
            ]);

            if ($isWriter) {
                $user->profile()->create([
                    'username' => $this->generateUniqueUsername($data->name),
                    'is_verified' => true,
                ]);
            }

            $user->notify(new WelcomeNotification($data->password));

            $user->skipVerificationEmail = true;

            event(new Registered($user));

            return $user;
        });
    }

    private function generateUniqueUsername(string $name): string
    {
        $base = Str::slug(Str::replace('@', '', $name));

        return $base . '-' . Str::lower(Str::random(4));
    }

    private function validateRole(string $role): string
    {
        $allowedRoles = [
            RoleEnum::WRITER->value,
            RoleEnum::READER->value,
        ];

        return in_array($role, $allowedRoles, true)
            ? $role
            : RoleEnum::READER->value;
    }
}
