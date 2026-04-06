<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum ThemePlatformEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::LIGHT => __('enums.theme.light'),
            self::DARK => __('enums.theme.dark'),
            self::SYSTEM => __('enums.theme.system'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LIGHT => 'yellow',
            self::DARK => 'gray',
            self::SYSTEM => 'blue',
        };
    }

    public static function defaultPrimary(): string {
        return '#18181b';
    }
    public static function defaultAccent(): string {
        return '#3f3f46';
    }

    case LIGHT = 'light';
    case DARK = 'dark';
    case SYSTEM = 'system';
}
