<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Reports;

use App\Actions\Reports\{ListReportsAction, HandleReportAction};
use App\DTOs\ReportFilterData;
use App\Enums\ReportStatusEnum;
use App\Enums\ReportReasonEnum;
use App\Livewire\Forms\Admin\ReportFilterForm;
use App\Models\Report;
use Illuminate\View\View;
use Livewire\Attributes\{Layout, Title, Computed};
use Livewire\{Component, WithPagination};
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Denúncias', 'subheading' => 'Moderação de conteúdo e usuários'])]
#[Title('Gestão de Denúncias')]
class ReportIndex extends Component
{
    use WithPagination;

    public ReportFilterForm $filters;
    public ?int $selectedReportId = null;

    public ?Report $activeReport = null;
    public string $adminFeedback = '';
    public string $selectedStatus = 'reviewed';
    public bool $shouldBanUser = false;
    public string $banReason = '';

    public function updateSearch()
    {
        $this->updateSearch();
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        $this->filters->direction = ($this->filters->sort === $column && $this->filters->direction === 'asc') ? 'desc' : 'asc';
        $this->filters->sort = $column;
    }

    public function openResponseModal(int $reportId): void
    {
        $this->activeReport = Report::find($reportId);
        $this->adminFeedback = $this->activeReport->admin_feedback ?? '';
        $this->selectedStatus = $this->activeReport->status->value;
        $this->dispatch('open-modal', name: 'report-response-modal');
    }

    public function submitResponse(HandleReportAction $action): void
    {
        $this->validate([
            'adminFeedback' => 'required|min:5',
            'selectedStatus' => 'required',
            'banReason' => 'required_if:shouldBanUser,true'
        ]);

        $action->exec(
            report: $this->activeReport,
            reviewer: auth()->user(),
            newStatus: ReportStatusEnum::from($this->selectedStatus),
            feedback: $this->adminFeedback,
            banUser: $this->shouldBanUser,
            banReason: $this->banReason
        );

        $this->reset(['activeReport', 'adminFeedback', 'shouldBanUser', 'banReason']);
        $this->dispatch('close-modal', name: 'report-response-modal');
        Toaster::success('Denúncia processada e usuários notificados!');
    }

    public function deleteReport(int $reportId): void
    {
        Report::findOrFail($reportId)->delete();
        Toaster::success('Registro de denúncia excluído.');
    }

    #[Computed]
    public function reports()
    {
        return app(ListReportsAction::class)->exec(
            ReportFilterData::fromArray($this->filters->all())
        );
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.reports.report-index');
    }
}
