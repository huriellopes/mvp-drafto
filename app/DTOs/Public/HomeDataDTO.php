<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
