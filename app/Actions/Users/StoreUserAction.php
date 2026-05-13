<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\SaveUserData;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class StoreUserAction
{
    /**
     * Sênior: Cria um usuário utilizando DTO e dispara eventos de sistema.
     */
    public function exec(SaveUserData $data): User
    {
        return DB::transaction(function () use ($data) {
            $isWriter = ($data->role instanceof RoleEnum)
                ? $data->role === RoleEnum::WRITER
                : $data->role === RoleEnum::WRITER->value;

            $user = User::create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
                'role' => $data->role,
                'status' => $data->status,
                'is_lifetime' => $data->is_lifetime,
                'plan_id' => $data->plan_id ?? ($isWriter ? 3 : null),
                'trial_ends_at' => ($isWriter && !$data->plan_id) ? now()->addDays(30) : null,
            ]);

            $user->profile()->create([
                'username' => Str::slug($user->name) . '-' . Str::lower(Str::random(4)),
            ]);

            // Sênior: Se for registro via formulário ou solicitado, dispara evento padrão do Laravel
            // Isso permite que Listeners cuidem de e-mails, logs e integrações externas.
            if ($data->send_welcome_email) {
                event(new Registered($user));
            }

            return $user;
        });
    }
}
