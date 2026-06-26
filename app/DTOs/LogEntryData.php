<?php

declare(strict_types=1);

namespace App\DTOs;

use Spatie\LaravelData\Data;

class LogEntryData extends Data
{
    public function __construct(
        public string $level,
        public string $loggedAt,
        public string $summary,
        public string $details,
    ) {}

    /**
     * Cor do badge (mapeada para as cores do componente x-ui.badge).
     */
    public function color(): string
    {
        return match (mb_strtolower($this->level)) {
            'emergency', 'alert', 'critical', 'error' => 'red',
            'warning' => 'orange',
            'notice', 'info' => 'blue',
            default => 'gray',
        };
    }
}
