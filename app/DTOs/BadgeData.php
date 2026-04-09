<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

final class BadgeData extends Data
{
    public function __construct(
        public string $theme = 'dark', // dark, light, colorful
        public bool $showStats = true,
        public bool $showBio = true,
        public bool $showLocation = true,
        public string $borderRadius = '3xl',
    ) {}
}
