<?php

declare(strict_types=1);

namespace App\DTOs\Public;

use Spatie\LaravelData\Data;

final readonly class StoreSiteViewData extends Data
{
    public function __construct(
        public ?int $userId,
        public string $url,
        public ?string $ipAddress,
        public ?string $userAgent,
        public ?string $sessionId,
        public ?string $searchQuery,
        public int $duration = 0,
    ) {}
}
