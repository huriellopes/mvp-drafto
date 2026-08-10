<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\RoleEnum;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class ImpersonateUserAction
{
    /**
     * Sênior: Executa a impersonação de um usuário.
     * Somente usuários com permissão podem realizar esta ação.
     */
    public function exec(User $targetUser): bool
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Segurança: Apenas Super Admin pode impersonar outros
        if (!$currentUser || !$currentUser->hasRole(RoleEnum::SUPER_ADMIN)) {
            return false;
        }

        // Segurança: Evitar impersonar a si mesmo (redundante mas seguro)
        if ($currentUser->id === $targetUser->id) {
            return false;
        }

        // Segurança: um super_admin não pode impersonar outro super_admin —
        // evita escalonamento cruzado e ações atribuídas ao alvo sem ele
        // saber, mesmo entre contas do mesmo nível de privilégio.
        if ($targetUser->hasRole(RoleEnum::SUPER_ADMIN)) {
            return false;
        }

        // Salva o ID do administrador original na sessão
        Session::put('impersonator_id', $currentUser->id);

        // Auditoria: Registra o início da impersonação
        ImpersonationLog::create([
            'impersonator_id' => $currentUser->id,
            'impersonated_id' => $targetUser->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'started_at' => now(),
        ]);

        // Realiza o login como o usuário alvo
        Auth::login($targetUser);

        // Segurança: regenera o ID de sessão na troca de identidade, como já
        // é feito em todo outro ponto de login do app (proteção contra
        // session fixation).
        session()->regenerate();

        return true;
    }
}
