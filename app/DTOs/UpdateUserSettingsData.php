<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UpdateUserSettingsData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password = null,
    ) {}
}
