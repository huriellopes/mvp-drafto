<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class SaveCommentData extends Data
{
    public function __construct(
        public string $content,
        public ?int $parent_id = null,
    ) {}
}
