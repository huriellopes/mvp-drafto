<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum ReportStatusEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::REVIEWED => 'Em Análise',
            self::DISMISSED => 'Arquivado / Ignorado',
            self::ACTION_TAKEN => 'Punição Aplicada',
            self::ACKNOWLEDGED => 'Agradecido / Ciente',
            self::IN_PLANNING => 'Em Planejamento',
            self::IMPLEMENTED => 'Implementado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::REVIEWED => 'blue',
            self::DISMISSED => 'gray',
            self::ACTION_TAKEN => 'red',
            self::ACKNOWLEDGED => 'green',
            self::IN_PLANNING => 'purple',
            self::IMPLEMENTED => 'indigo',
        };
    }

    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case DISMISSED = 'dismissed';
    case ACTION_TAKEN = 'action_taken';
    case ACKNOWLEDGED = 'acknowledged';
    case IN_PLANNING = 'in_planning';
    case IMPLEMENTED = 'implemented';
}
