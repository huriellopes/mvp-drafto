<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class DailySummaryData extends Data
{
    public function __construct(
        public int $newUsers,
        public int $newPosts,
        public int $newComments,
        public int $newFollowers,
        public int $newSubscribers,
        public int $newReports,
        public int $failedJobs,
    ) {}
}
