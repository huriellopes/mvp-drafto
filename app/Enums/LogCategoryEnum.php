<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum LogCategoryEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::SECURITY => 'Segurança',
            self::PAYMENT => 'Pagamentos',
            self::QUEUE => 'Filas/Jobs',
            self::SYSTEM => 'Sistema',
            self::PERFORMANCE => 'Performance',
            self::AUDIT => 'Auditoria',
        };
    }

    case SECURITY = 'security';
    case PAYMENT = 'payment';
    case QUEUE = 'queue';
    case SYSTEM = 'system';
    case PERFORMANCE = 'performance';
    case AUDIT = 'audit';
}
