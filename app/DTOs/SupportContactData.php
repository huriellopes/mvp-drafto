<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class SupportContactData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public string $subject,
        public string $message,
    ) {}
}
