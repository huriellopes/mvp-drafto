<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PostCollectionVisibilityEnum;
use Spatie\LaravelData\Data;

class PostCollectionData extends Data
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
        public PostCollectionVisibilityEnum $visibility = PostCollectionVisibilityEnum::PRIVATE,
    ) {}
}
