<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class FollowersFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public string $sort = 'followers.created_at',
        public string $direction = 'desc',
    ) {}
}
