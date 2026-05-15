<?php

declare(strict_types=1);

namespace App\Enums;

enum SupportStatusEnum: string
{
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::IN_PROGRESS => 'Em Resolução',
            self::BLOCKED => 'Bloqueado',
            self::RESOLVED => 'Resolvido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_PROGRESS => 'blue',
            self::BLOCKED => 'red',
            self::RESOLVED => 'green',
        };
    }
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case RESOLVED = 'resolved';
}
