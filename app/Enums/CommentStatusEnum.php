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
            self::PENDING => __('enums.comment_status.pending'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VISIBLE => 'O comentário está visível para todos os usuários e visitantes.',
            self::HIDDEN => 'O comentário foi ocultado e não aparece publicamente.',
            self::PENDING => 'O comentário está aguardando revisão da moderação.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VISIBLE => 'green',
            self::HIDDEN => 'danger',
            self::PENDING => 'amber',
        };
    }

    case VISIBLE = 'visible';
    case HIDDEN = 'hidden';
    case PENDING = 'pending';
}
