<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

final class ExportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $exportClass,
        private readonly array $params,
        private readonly string $fileName,
        private readonly string $disk = 'local',
    ) {}

    public function handle(): void
    {
        $export = app()->makeWith($this->exportClass, $this->params);
        $path = 'temp/' . $this->fileName;

        Excel::store($export, $path, $this->disk);
    }
}
