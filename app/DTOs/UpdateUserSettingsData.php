<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UpdateUserSettingsData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $email_verified_at = null,
        public ?string $password = null,
    ) {}
}
