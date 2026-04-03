<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum ReportStatusEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums.report_status.pending'),
            self::REVIEWED => __('enums.report_status.reviewed'),
            self::DISMISSED => __('enums.report_status.dismissed'),
            self::ACTION_TAKEN => __('enums.report_status.action_taken'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::REVIEWED => 'blue',
            self::DISMISSED => 'gray',
            self::ACTION_TAKEN => 'red',
        };
    }

    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case DISMISSED = 'dismissed';
    case ACTION_TAKEN = 'action_taken';
}
