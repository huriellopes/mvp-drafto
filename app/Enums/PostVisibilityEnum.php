<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum PostVisibilityEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => __('enums.post_visibility.public'),
            self::UNLISTED => __('enums.post_visibility.unlisted'),
            self::FOLLOWERS_ONLY => __('enums.post_visibility.followers_only'),
            self::PREMIUM => 'Premium (Assinantes)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PUBLIC => 'green',
            self::UNLISTED => 'yellow',
            self::FOLLOWERS_ONLY => 'blue',
            self::PREMIUM => 'purple',
        };
    }

    case PUBLIC = 'public';
    case UNLISTED = 'unlisted';
    case FOLLOWERS_ONLY = 'followers_only';
    case PREMIUM = 'premium';
}
