<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

final class SupportData extends Data
{
    public function __construct(
        public string $subject,
        public string $message,
        public ?string $admin_response = null,
        public ?string $status = null,
    ) {}
}
