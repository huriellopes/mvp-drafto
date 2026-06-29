<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum RoleEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('enums.role.super_admin'),
            self::WRITER => __('enums.role.writer'),
            self::READER => __('enums.role.reader'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'red',
            self::WRITER => 'blue',
            self::READER => 'gray',
        };
    }

    public static function assignableOptions(): array
    {
        return collect(self::options())
            ->reject(fn ($option) => $option['value'] === self::SUPER_ADMIN->value)
            ->values()
            ->all();
    }

    case SUPER_ADMIN = 'super_admin';
    case WRITER = 'writer';
    case READER = 'reader';
}
