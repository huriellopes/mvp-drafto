<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\SocialPlatformEnum;
use Spatie\LaravelData\Data;

class SocialPlatformData extends Data
{
    public function __construct(
        public string $value,
        public string $label,
        public string $icon,
        public string $color,
    ) {}

    public static function fromEnum(SocialPlatformEnum $platform): self
    {
        return new self(
            value: $platform->value,
            label: $platform->label(),
            icon: $platform->icon(),
            color: $platform->color(),
        );
    }
}
