<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserStatusEnum;
use App\Models\MagicLoginToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

final class LoginViaMagicLinkAction
{
    public const RESULT_INVALID = 'invalid';

    public const RESULT_TWO_FACTOR = 'two-factor';

    public const RESULT_SUCCESS = 'success';

    /**
     * Consome um token de link mágico e procede com o login.
     *
     * Espelha o desfecho do AuthenticateUserAction: respeita o 2FA (delegando
     * ao desafio) e as checagens de conta (inativa/banida).
     *
     * @return self::RESULT_*
     */
    public function exec(string $plainToken): string
    {
        $record = MagicLoginToken::query()
            ->where('token', MagicLoginToken::hashToken($plainToken))
            ->first();

        if (!$record) {
            return self::RESULT_INVALID;
        }

        $expired = $record->isExpired();
        $user = $record->user;

        // Uso único: o token é descartado assim que tocado, válido ou não.
        $record->delete();

        if ($expired || !$user) {
            return self::RESULT_INVALID;
        }

        if ($user->status === UserStatusEnum::INACTIVE || $user->banned_until?->isFuture()) {
            return self::RESULT_INVALID;
        }

        // Se o usuário tem 2FA, o link mágico não pula a segunda etapa:
        // preparamos a sessão e delegamos ao desafio existente.
        if ($user->hasTwoFactorEnabled()) {
            session([
                'auth.2fa.id' => $user->id,
                'auth.2fa.remember' => false,
            ]);

            return self::RESULT_TWO_FACTOR;
        }

        Auth::login($user);
        session()->regenerate();

        $user->update([
            'last_login_at' => Carbon::now(),
            'ip_address' => request()->ip(),
            'reengagement_stage' => null,
            'reengagement_sent_at' => null,
        ]);

        return self::RESULT_SUCCESS;
    }
}
