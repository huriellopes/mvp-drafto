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
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', [
    'heading' => 'Analytics do Site',
    'subheading' => 'Monitoramento de visitas do site.',
])]
#[Title('Analytics do Site')]
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
        return resolve(GetSiteAnalyticsAction::class)
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

        dispatch(new ExportDataJob(SiteAnalyticsExport::class, [
            'days' => $this->days,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ], $fileName));

        Toaster::info('O relatório de analytics está sendo gerado...');
    }

    public function render(): View
    {
        // Segurança: render() roda em toda chamada de método Livewire
        // (wire:click/wire:submit), não só na carga inicial da página —
        // é o único ponto que reautoriza de fato uma sessão que perdeu o
        // papel super_admin no meio da sessão (o middleware `can:admin`
        // da rota só protege o GET inicial).
        $this->authorize('admin');

        return view('livewire.dashboard.admin.analytics.site-analytics');
    }
}
