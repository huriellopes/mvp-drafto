<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Analytics;

use App\Actions\Admin\GetSiteAnalyticsAction;
use App\Exports\SiteAnalyticsExport;
use App\Jobs\ExportDataJob;
use App\Livewire\Traits\WithBackgroundExport;
use Exception;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app')]
final class SiteAnalytics extends Component
{
    use WithBackgroundExport;

    public int $days = 7;

    public ?string $startDate = null;

    public ?string $endDate = null;

    /**
     * @throws Exception
     */
    #[Computed]
    public function analytics()
    {
        return app(GetSiteAnalyticsAction::class)
            ->handle(
                $this->days,
                $this->startDate,
                $this->endDate,
            );
    }

    public function export(): void
    {
        $fileName = 'site-analytics-' . now()->format('Y-m-d-His') . '.xlsx';
        $this->generatedPath = 'temp/' . $fileName;

        ExportDataJob::dispatch(
            SiteAnalyticsExport::class,
            [
                'days' => $this->days,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ],
            $fileName,
        );

        Toaster::info('O relatório de analytics está sendo gerado...');
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.analytics.site-analytics');
    }
}
