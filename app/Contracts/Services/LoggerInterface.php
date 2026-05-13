<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Enums\LogCategoryEnum;

interface LoggerInterface
{
    /**
     * Log an informative message.
     *
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, LogCategoryEnum $category = LogCategoryEnum::SYSTEM, array $context = []): void;

    /**
     * Log an error message.
     *
     * @param  array<string, mixed>  $context
     */
    public function error(string $message, LogCategoryEnum $category = LogCategoryEnum::SYSTEM, array $context = []): void;

    /**
     * Log a warning message.
     *
     * @param  array<string, mixed>  $context
     */
    public function warning(string $message, LogCategoryEnum $category = LogCategoryEnum::SYSTEM, array $context = []): void;
}
