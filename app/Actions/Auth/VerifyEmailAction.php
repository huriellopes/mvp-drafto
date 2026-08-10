<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

final class VerifyEmailAction
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function exec(Request $request): bool
    {
        $user = User::findOrFail($request->route('id'));

        // Segurança: confere que quem está autenticado é o dono do link
        // (padrão do Laravel em EmailVerificationRequest::authorize(), que
        // esta implementação customizada não replicava). A rota já é
        // assinada, então um atacante não forja o link sozinho — mas se o
        // link de outra pessoa vazar (encaminhado, print, log), sem este
        // check qualquer usuário logado poderia verificar o e-mail alheio.
        if (!$request->user() || (int) $request->user()->id !== (int) $user->id) {
            return false;
        }

        if (!hash_equals((string) $request->route('hash'), sha1((string) $user->getEmailForVerification()))) {
            return false;
        }

        if ($user->hasVerifiedEmail()) {
            return true;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return true;
    }
}
