<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\AuthenticateData;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\ValidationException;

final class AuthenticateUserAction
{
    /**
     * @throws AuthenticationException
     */
    public function exec(AuthenticateData $data): bool|string
    {
        $user = User::query()
            ->where('email', $data->email)
            ->first();

        // Sênior: Se o usuário tem 2FA, primeiro validamos as credenciais sem logar
        if ($user && $user->hasTwoFactorEnabled()) {
            if (!Auth::validate(['email' => $data->email, 'password' => $data->password])) {
                return false;
            }

            // Guardamos o ID na sessão temporária para o desafio
            session([
                'auth.2fa.id' => $user->id,
                'auth.2fa.remember' => $data->remember,
            ]);

            return 'two-factor';
        }

        if (!Auth::attempt(['email' => $data->email, 'password' => $data->password], $data->remember)) {
            return false;
        }

        /** @var User $loggedUser */
        $loggedUser = Auth::user();

        if ($loggedUser->status === UserStatusEnum::INACTIVE) {
            Auth::logout();

            throw ValidationException::withMessages(['email' => 'Sua conta ainda não foi ativada.']);
        }

        if ($loggedUser->banned_until?->isFuture()) {
            Auth::logout();
            $days = now()->diffInDays($loggedUser->banned_until);

            throw ValidationException::withMessages(['email' => "Acesso suspenso por mais {$days} dias."]);
        }

        // Sessão única apenas quando o usuário NÃO pediu para ser lembrado:
        // um login sem "lembrar-me" encerra as sessões dos outros dispositivos.
        // Com "lembrar-me", preservamos os demais dispositivos (multi-device
        // persistente) e mantemos este login ativo entre sessões.
        if (!$data->remember && $data->password !== '' && $data->password !== '0') {
            Auth::logoutOtherDevices($data->password);
        }

        session()->regenerate();

        $loggedUser->update([
            'last_login_at' => Date::now(),
            'ip_address' => request()->ip(),
            // O usuário voltou: zera o ciclo de e-mails de retorno para que ele
            // possa ser reengajado de novo caso fique inativo no futuro.
            'reengagement_stage' => null,
            'reengagement_sent_at' => null,
        ]);

        return true;
    }
}
