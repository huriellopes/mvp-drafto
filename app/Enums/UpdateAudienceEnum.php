<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum UpdateAudienceEnum: string
{
    use EnumOptions;

    public function label(): string
    {
        return match ($this) {
            self::ALL => __('enums.update_audience.all'),
            self::WRITERS => __('enums.update_audience.writers'),
            self::READERS => __('enums.update_audience.readers'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ALL => __('enums.update_audience.description.all'),
            self::WRITERS => __('enums.update_audience.description.writers'),
            self::READERS => __('enums.update_audience.description.readers'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ALL => 'gray',
            self::WRITERS => 'blue',
            self::READERS => 'emerald',
        };
    }

    case ALL = 'all';
    case WRITERS = 'writers';
    case READERS = 'readers';
}
