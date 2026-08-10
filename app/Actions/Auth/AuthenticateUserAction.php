<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\AuthenticateData;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthenticateUserAction
{
    // Hash bcrypt (custo 12, igual ao BCRYPT_ROUNDS padrão do app) de um
    // valor arbitrário — usado só para equalizar o tempo de resposta quando
    // o e-mail não existe (ver comentário abaixo). Não corresponde a
    // nenhuma senha real.
    private const string DUMMY_HASH = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    /**
     * @throws AuthenticationException
     */
    public function exec(AuthenticateData $data): bool|string
    {
        $user = User::query()
            ->where('email', $data->email)
            ->first();

        if (!$user) {
            // Segurança: equaliza o tempo de resposta com o de uma tentativa
            // de senha incorreta real. Auth::attempt() não chega a rodar
            // Hash::check() quando o e-mail não existe, criando uma
            // diferença de tempo mensurável que permite enumerar contas.
            Hash::check($data->password, self::DUMMY_HASH);

            return false;
        }

        // Sênior: Se o usuário tem 2FA, primeiro validamos as credenciais sem logar
        if ($user->hasTwoFactorEnabled()) {
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

        // Nota: o listener EnforceSingleSession (evento Login) já encerra as
        // sessões de banco de outros dispositivos em TODO login, independente
        // de "lembrar-me" — política real do app é sessão única sempre.
        // Auth::logoutOtherDevices() aqui é redundante para esse fim (é o
        // listener quem faz o trabalho), mas ainda é o mecanismo correto do
        // Laravel para invalidar sessões baseadas em cookie assinado (guard
        // "remember") de outros dispositivos quando a senha muda de contexto.
        // "Lembrar-me" só afeta a rotação do remember_token (ver listener),
        // não a política de sessão única.
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
