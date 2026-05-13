<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum PostStatusEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('enums.post_status.draft'),
            self::PUBLISHED => __('enums.post_status.published'),
            self::ARCHIVED => __('enums.post_status.archived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'yellow',
            self::PUBLISHED => 'green',
            self::ARCHIVED => 'gray',
        };
    }

    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
