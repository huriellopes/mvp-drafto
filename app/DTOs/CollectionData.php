<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CollectionData
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
    ) {}
}
