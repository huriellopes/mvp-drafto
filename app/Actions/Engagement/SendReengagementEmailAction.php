<?php

declare(strict_types=1);

namespace App\Actions\Engagement;

use App\Mail\ReengagementMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

final class SendReengagementEmailAction
{
    /**
     * Faixas (em dias) do e-mail escalonado de retorno.
     */
    public const array STAGES = [15, 30, 60];

    /**
     * Envia (enfileira) o e-mail de retorno para um usuário.
     *
     * @param  int|null  $stage  faixa específica; se null, é resolvida pela inatividade.
     * @param  bool  $force  ignora cooldown/faixa-já-enviada (uso manual pelo admin).
     * @return bool true se o e-mail foi enfileirado.
     */
    public function exec(User $user, ?int $stage = null, bool $force = false): bool
    {
        // No envio manual (force) o admin decide — não exigimos e-mail verificado.
        if (!$this->isEligible($user, requireVerified: !$force)) {
            return false;
        }

        $inactiveDays = $user->inactiveDays();
        $stage ??= $this->resolveStage($inactiveDays);

        // Sem faixa atingida e sem forçar: nada a enviar.
        if ($stage === null && !$force) {
            return false;
        }

        // Não reenvia a mesma faixa (ou faixa anterior) já disparada — exceto no modo forçado.
        if (!$force && $user->reengagement_stage !== null && $user->reengagement_stage >= $stage) {
            return false;
        }

        Mail::to($user->email)->send(new ReengagementMail($user, $stage ?? 0, $inactiveDays));

        $user->forceFill([
            'reengagement_sent_at' => now(),
            'reengagement_stage' => $stage ?? $user->reengagement_stage,
        ])->save();

        return true;
    }

    /**
     * Usuário pode receber o e-mail de retorno?
     *
     * @param  bool  $requireVerified  exige e-mail verificado (envio automático);
     *                                 no envio manual pelo admin, não é exigido.
     */
    public function isEligible(User $user, bool $requireVerified = true): bool
    {
        return $user->isActive()
            && (bool) $user->wants_reengagement_emails
            && !$user->banned_until?->isFuture()
            && (!$requireVerified || $user->hasVerifiedEmail());
    }

    /**
     * Maior faixa atingida pela quantidade de dias inativo.
     */
    public function resolveStage(int $inactiveDays): ?int
    {
        foreach (array_reverse(self::STAGES) as $threshold) {
            if ($inactiveDays >= $threshold) {
                return $threshold;
            }
        }

        return null;
    }
}
