<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Public\StoreSiteViewAction;
use App\DTOs\Public\StoreSiteViewData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ProcessSiteViewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly StoreSiteViewData $data,
    ) {}

    public function handle(StoreSiteViewAction $action): void
    {
        $action->handle($this->data);
    }
}
