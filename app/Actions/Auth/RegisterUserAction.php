<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Mail\TrialStartedNotification;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

final class RegisterUserAction
{
    /**
     * @param  array{name: string, email: string, password: string, role: string}  $data
     *
     * @throws Throwable
     */
    public function exec(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $role = $this->validateRole($data['role']);
            $isWriter = ($role === RoleEnum::WRITER->value);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $role,
                'status' => UserStatusEnum::ACTIVE,
                'ip_address' => request()->ip(),
                'last_login_at' => Carbon::now(),
                'plan_id' => $isWriter ? 3 : null, // Sênior: Apenas Escritores iniciam no Pro (ID 3)
                'trial_ends_at' => $isWriter ? now()->addDays(15) : null,
            ]);

            $user->profile()->create([
                'username' => $this->generateUniqueUsername($data['name']),
            ]);

            event(new Registered($user));

            if ($isWriter) {
                Mail::to($user->email)->queue(new TrialStartedNotification($user));
            }

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
