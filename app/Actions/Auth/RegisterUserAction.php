<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\RegisterUserData;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Notifications\Auth\WelcomeNotification;
use App\Traits\GeneratesUsername;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RegisterUserAction
{
    use GeneratesUsername;

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
                'last_login_at' => Date::now(),
            ]);

            if ($isWriter) {
                $user->profile()->create([
                    'name' => $data->name,
                    'email' => $data->email,
                    'username' => $this->generateUniqueUsername($data->name),
                    'is_verified' => true,
                ]);
            }

            // Segurança: não envia a senha de volta por e-mail (mesmo sendo a
            // que o próprio usuário escolheu) — evita uma cópia em texto
            // puro trafegando por SMTP e parada na caixa de entrada/logs do
            // provedor de e-mail.
            $user->notify(new WelcomeNotification);

            $user->skipVerificationEmail = true;

            event(new Registered($user));

            Log::channel('telegram_support')->info("✅ Novo usuário registrado: **{$user->name}**", [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->label(),
                'status' => $user->status->label(),
                'ip_address' => $user->ip_address,
            ]);

            return $user;
        });
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
