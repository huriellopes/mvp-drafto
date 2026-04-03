<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum PostTypeEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::POST => __('enums.post_type.post'),
            self::ARTICLE => __('enums.post_type.article'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::POST => 'blue',
            self::ARTICLE => 'green',
        };
    }

    case POST = 'post';
    case ARTICLE = 'article';
}
