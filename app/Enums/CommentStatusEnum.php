<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum CommentStatusEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::VISIBLE => __('enums.comment_status.visible'),
            self::HIDDEN => __('enums.comment_status.hidden'),
            self::BLOCKED => __('enums.comment_status.blocked'),
            self::PENDING => __('enums.comment_status.pending'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VISIBLE => 'green',
            self::HIDDEN, self::PENDING => 'yellow',
            self::BLOCKED => 'red',
        };
    }

    case VISIBLE = 'visible';
    case HIDDEN = 'hidden';
    case BLOCKED = 'blocked';
    case PENDING = 'penging';
}
