<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

readonly class HomeDataDTO
{
    public function __construct(
        public int $totalPosts,
        public int $totalUsers,
        public Collection $featuredWriters,
        public LengthAwarePaginator $posts,
        public Collection $categories,
    ) {}
}
