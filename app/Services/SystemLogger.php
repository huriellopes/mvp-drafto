<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\LoggerInterface;
use App\Enums\LogCategoryEnum;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class SystemLogger implements LoggerInterface
{
    /**
     * Log an informative message with structured context.
     *
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, LogCategoryEnum $category = LogCategoryEnum::SYSTEM, array $context = []): void
    {
        $this->log('info', $message, $category, $context);
    }

    /**
     * Log an error message with structured context.
     *
     * @param  array<string, mixed>  $context
     */
    public function error(string $message, LogCategoryEnum $category = LogCategoryEnum::SYSTEM, array $context = []): void
    {
        $this->log('error', $message, $category, $context);
    }

    /**
     * Log a warning message with structured context.
     *
     * @param  array<string, mixed>  $context
     */
    public function warning(string $message, LogCategoryEnum $category = LogCategoryEnum::SYSTEM, array $context = []): void
    {
        $this->log('warning', $message, $category, $context);
    }

    /**
     * Core logging logic.
     *
     * @param  array<string, mixed>  $context
     */
    private function log(string $level, string $message, LogCategoryEnum $category, array $context): void
    {
        $structuredContext = array_merge([
            'category' => $category->value,
            'trace_id' => session()->get('trace_id') ?? Str::uuid()->toString(),
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'url' => request()->fullUrl(),
        ], $context);

        // Envia para o canal específico se configurado, caso contrário usa o stack padrão
        $channel = $this->resolveChannel($category);

        Log::channel($channel)->$level("[{$category->label()}] $message", $structuredContext);
    }

    /**
     * Resolve the appropriate log channel based on category.
     */
    private function resolveChannel(LogCategoryEnum $category): string
    {
        return match ($category) {
            LogCategoryEnum::SECURITY => 'security',
            LogCategoryEnum::PAYMENT => 'payments',
            LogCategoryEnum::QUEUE => 'jobs',
            default => config('logging.default') ?? 'stack',
        };
    }
}
