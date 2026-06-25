<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum LinkVisibilityEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => __('enums.link_visibility.public'),
            self::PRIVATE => __('enums.link_visibility.private'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PUBLIC => 'emerald',
            self::PRIVATE => 'zinc',
        };
    }

    case PUBLIC = 'public';
    case PRIVATE = 'private';
}
