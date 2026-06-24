<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum PostCollectionVisibilityEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::PRIVATE => 'Privada',
            self::PUBLIC => 'Pública',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PRIVATE => 'Apenas você vê esta coleção no seu painel.',
            self::PUBLIC => 'Visível para qualquer pessoa no seu perfil.',
        };
    }

    case PRIVATE = 'private';
    case PUBLIC = 'public';
}
