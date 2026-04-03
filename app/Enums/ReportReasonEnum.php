<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum ReportReasonEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::SPAM => __('enums.report_reason.spam'),
            self::ABUSE => __('enums.report_reason.abuse'),
            self::HARASSMENT => __('enums.report_reason.harassment'),
            self::PLAGIARISM => __('enums.report_reason.plagiarism'),
            self::INAPPROPRIATE => __('enums.report_reason.inappropriate'),
            self::OTHER => __('enums.report_reason.other'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SPAM => 'yellow',
            self::ABUSE, self::INAPPROPRIATE, self::HARASSMENT => 'red',
            self::PLAGIARISM => 'orange',
            self::OTHER => 'gray',
        };
    }

    case SPAM = 'spam';
    case ABUSE = 'abuse';
    case HARASSMENT = 'harassment';
    case PLAGIARISM = 'plagiarism';
    case INAPPROPRIATE = 'inappropriate';
    case OTHER = 'other';
}
