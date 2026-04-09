<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

final class AuthenticateData extends Data
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}
}
