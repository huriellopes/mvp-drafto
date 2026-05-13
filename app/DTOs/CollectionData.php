<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class CollectionData extends Data
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
    ) {}
}
