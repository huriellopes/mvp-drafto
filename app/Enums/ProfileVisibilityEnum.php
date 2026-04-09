<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum ProfileVisibilityEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Público',
            self::PRIVATE => 'Privado',
            self::UNLISTED => 'Não Listado',
        };
    }

    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case UNLISTED = 'unlisted';
}
