<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum UserStatusEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('enums.user_status.active'),
            self::SUSPENDED => __('enums.user_status.suspended'),
            self::PENDING => __('enums.user_status.pending'),
            self::INACTIVE => __('enums.user_status.inactive'),
            self::BANNED => __('enums.user_status.banned'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::SUSPENDED => 'yellow',
            self::PENDING => 'orange',
            self::INACTIVE, self::BANNED => 'red',
        };
    }

    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';
    case INACTIVE = 'inactive';
    case BANNED = 'banned';
}
