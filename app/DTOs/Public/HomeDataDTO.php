<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class HomeDataDTO extends Data
{
    public function __construct(
        public int $totalPosts,
        public int $totalUsers,
        public Collection $featuredWriters,
        public LengthAwarePaginator $posts,
        public Collection $categories,
    ) {}
}
