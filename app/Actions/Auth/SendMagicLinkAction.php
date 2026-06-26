<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserStatusEnum;
use App\Models\MagicLoginToken;
use App\Models\User;
use App\Notifications\Auth\MagicLinkNotification;
use Illuminate\Support\Str;

final class SendMagicLinkAction
{
    /**
     * Gera e envia um link mágico de login para o e-mail informado.
     *
     * Resposta neutra: nunca revela se o e-mail existe (proteção contra
     * enumeração de contas). Apenas contas existentes e não inativas recebem.
     */
    public function exec(string $email, bool $remember = false): void
    {
        $user = User::query()->where('email', $email)->first();

        if (!$user || $user->status === UserStatusEnum::INACTIVE) {
            return;
        }

        // Invalida links mágicos anteriores deste usuário (uso único efetivo).
        $user->magicLoginTokens()->delete();

        $plainToken = Str::random(64);

        $user->magicLoginTokens()->create([
            'token' => MagicLoginToken::hashToken($plainToken),
            'expires_at' => MagicLoginToken::expiresAt(),
            'remember' => $remember,
        ]);

        $user->notify(new MagicLinkNotification($plainToken));
    }
}
