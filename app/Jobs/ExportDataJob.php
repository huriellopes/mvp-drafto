<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\Exports\ExportFailed;
use App\Events\Exports\ExportReady;
use App\Support\TemporaryExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

final class ExportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Large exports must not compete with e-mails/notifications, so they run
     * on a dedicated queue with its own (limited) Horizon workers.
     */
    public const string QUEUE = 'exports';

    public const string CONNECTION = 'redis-exports';

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        private readonly string $exportClass,
        private readonly array $params,
        private readonly string $fileName,
        private readonly int|string $userId,
        private readonly string $disk = TemporaryExport::DISK,
    ) {
        $this->onQueue(self::QUEUE);

        // On Redis, use the connection whose retry_after outlasts $timeout.
        if (config('queue.default') === 'redis') {
            $this->onConnection(self::CONNECTION);
        }
    }

    public function handle(): void
    {
        $export = app()->makeWith($this->exportClass, $this->params);
        $path = $this->path();

        Excel::store($export, $path, $this->disk);

        // The file is already stored: a websocket outage must not fail the
        // job. The dashboard's fallback poll still picks the file up.
        rescue(fn () => event(new ExportReady($this->userId, $path)));
    }

    public function failed(?Throwable $exception): void
    {
        rescue(fn () => event(new ExportFailed($this->userId, $this->path())));
    }

    public function path(): string
    {
        return TemporaryExport::pathFor($this->userId, $this->fileName);
    }
}
