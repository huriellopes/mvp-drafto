<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class UpdateUserSettingsData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $email_verified_at = null,
        public ?string $password = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? null,
        );
    }
}
