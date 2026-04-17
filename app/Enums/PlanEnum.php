<?php

declare(strict_types=1);

namespace App\Enums;

enum PlanEnum: string
{
    case FREE = 'free';
    case PLUS = 'plus';
    case PRO = 'pro';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Plano Gratuito',
            self::PLUS => 'Plano Plus',
            self::PRO => 'Plano Pro',
        };
    }

    /**
     * Retorna todos os slugs para facilitar validações e seeders
     */
    public static function slugs(): array
    {
        return array_column(self::cases(), 'value');
    }
}
