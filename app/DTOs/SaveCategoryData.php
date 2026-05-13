<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class SaveCategoryData extends Data
{
    public function __construct(
        public ?int $user_id,
        public string $name,
        public string $slug,
        public ?string $description,
    ) {}
}
