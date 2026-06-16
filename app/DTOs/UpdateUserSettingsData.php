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
        public bool $wants_reengagement_emails = true,
        public bool $wants_product_updates = true,
    ) {}
}
