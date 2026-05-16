<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin;

use App\Actions\Admin\GetAuditsAction;
use App\DTOs\AuditFilterData;
use App\Exports\AuditsExport;
use App\Jobs\ExportDataJob;
use App\Livewire\Traits\WithBackgroundExport;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app')]
class AuditLogIndex extends Component
{
    use WithBackgroundExport, WithPagination;

    #[Url]
    public ?int $userId = null;

    #[Url]
    public ?string $event = null;

    #[Url]
    public ?string $auditableType = null;

    #[Url]
    public ?string $startDate = null;

    #[Url]
    public ?string $endDate = null;

    /**
     * Reset pagination when filters are updated.
     */
    public function updated(): void
    {
        $this->resetPage();
    }

    /**
     * Export audits to Excel using DTO and Action.
     */
    public function export(): void
    {
        $fileName = 'auditoria-' . now()->format('Y-m-d-His') . '.xlsx';
        $this->generatedPath = 'temp/' . $fileName;

        ExportDataJob::dispatch(
            AuditsExport::class,
            ['filters' => $this->filters],
            $fileName,
        );

        Toaster::info('O relatório de auditoria está sendo gerado...');
    }

    /**
     * Computed property to encapsulate filter data.
     */
    #[Computed]
    public function filters(): AuditFilterData
    {
        return new AuditFilterData(
            userId: $this->userId,
            event: $this->event,
            auditableType: $this->auditableType,
            startDate: $this->startDate,
            endDate: $this->endDate,
            perPage: 20,
        );
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $action = new GetAuditsAction();

        return view('livewire.dashboard.admin.audit-log-index', [
            'audits' => $action->exec($this->filters),
            'users' => $action->getAvailableUsers(),
            'events' => $action->getUniqueEvents(),
            'types' => $action->getUniqueTypes(),
        ]);
    }
}
