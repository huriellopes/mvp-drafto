<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

/**
 * Cálculo de atividade/inatividade do usuário (para reengajamento).
 */
trait TracksActivity
{
    /**
     * Data da última atividade considerada para inatividade: o mais recente
     * entre o último login, a última escrita (post) e a criação da conta.
     * Usa `posts_max_created_at` se já vier carregado via subquery (evita N+1).
     */
    public function lastActivityAt(): Carbon
    {
        $lastPostAt = $this->getAttribute('posts_max_created_at')
            ?? $this->posts()->max('created_at');

        $dates = array_filter([
            $this->last_login_at,
            $lastPostAt ? Date::parse($lastPostAt) : null,
            $this->created_at,
        ]);

        return $dates === [] ? now() : Date::parse(max($dates));
    }

    /**
     * Quantos dias o usuário está inativo (sem logar e sem escrever).
     */
    public function inactiveDays(): int
    {
        return (int) $this->lastActivityAt()->diffInDays(now());
    }
}
