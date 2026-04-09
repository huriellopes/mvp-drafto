<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use Spatie\LaravelData\Data;

final class ExploreWritersFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public int $perPage = 16,
    ) {}
}
