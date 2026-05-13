<?php

declare(strict_types=1);

namespace App\Enums;

enum StripeSubscriptionStatusEnum: string
{
    public function label(): string
    {
        return match ($this) {
            self::INCOMPLETE => 'Incompleta',
            self::INCOMPLETE_EXPIRED => 'Incompleta (Expirada)',
            self::TRIALING => 'Em Período de Teste',
            self::ACTIVE => 'Ativa',
            self::PAST_DUE => 'Atrasada',
            self::CANCELED => 'Cancelada',
            self::UNPAID => 'Não Paga',
            self::PAUSED => 'Pausada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE, self::TRIALING => 'green',
            self::PAST_DUE, self::INCOMPLETE, self::PAUSED => 'orange',
            self::CANCELED, self::UNPAID, self::INCOMPLETE_EXPIRED => 'red',
        };
    }
    case INCOMPLETE = 'incomplete';
    case INCOMPLETE_EXPIRED = 'incomplete_expired';
    case TRIALING = 'trialing';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case CANCELED = 'canceled';
    case UNPAID = 'unpaid';
    case PAUSED = 'paused';
}
