<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PostStatusEnum;
use Spatie\LaravelData\Data;

class PostFiltersData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?PostStatusEnum $status = null,
        public ?PostStatusEnum $notStatus = null,
        public string $sort = 'created_at',
        public string $direction = 'desc',
        public int $perPage = 10,
    ) {}
}
