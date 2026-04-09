<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Reports;

use App\Actions\Reports\HandleReportAction;
use App\DTOs\HandleReportData;
use App\DTOs\ReportFilterData;
use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use App\Livewire\Forms\Admin\ReportFilterForm;
use App\Models\Report;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Throwable;

#[Layout('layouts.app', ['heading' => 'Gestão e Moderação', 'subheading' => 'Análise de denúncias, elogios e feedbacks técnicos'])]
#[Title('Moderação de Conteúdo')]
class ReportIndex extends Component
{
    use WithPagination;

    public ReportFilterForm $filters;

    public string $tab = 'all'; // all, moderation, feedback

    public ?Report $activeReport = null;

    public string $adminFeedback = '';

    public string $selectedStatus = 'reviewed';

    public bool $shouldBanUser = false;

    public string $banReason = '';

    public ?int $reportIdBeingDeleted = null;

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->filters->resetFilters();
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        $this->filters->direction = ($this->filters->sort === $column && $this->filters->direction === 'asc') ? 'desc' : 'asc';
        $this->filters->sort = $column;
    }

    public function openResponseModal(int $reportId): void
    {
        $this->activeReport = Report::with('reportable')->find($reportId);

        // Sênior: Sincroniza o estado para o modal não abrir "vazio"
        $this->adminFeedback = $this->activeReport->admin_feedback ?? '';
        $this->selectedStatus = $this->activeReport->status->value;

        // Verifica se o usuário reportado já está banido ou se há ação pendente
        $this->shouldBanUser = $this->activeReport->status === ReportStatusEnum::ACTION_TAKEN;
        $this->banReason = $this->activeReport->admin_feedback ?? '';

        $this->dispatch('open-modal', name: 'report-response-modal');
    }

    public function confirmDelete(int $id): void
    {
        $this->reportIdBeingDeleted = $id;
        $this->dispatch('open-modal', name: 'confirm-report-deletion');
    }

    public function deleteReport(): void
    {
        Report::destroy($this->reportIdBeingDeleted);
        $this->reset('reportIdBeingDeleted');
        $this->dispatch('close-modal', name: 'confirm-report-deletion');
        Toaster::success('Denúncia removida do sistema.');
    }

    /**
     * @throws Throwable
     */
    public function submitResponse(HandleReportAction $action): void
    {
        $this->validate([
            'adminFeedback' => 'required|min:5',
            'selectedStatus' => 'required',
            'banReason' => 'required_if:shouldBanUser,true',
        ]);

        $data = HandleReportData::from([
            'reportId' => $this->activeReport->id,
            'status' => $this->selectedStatus,
            'feedback' => $this->adminFeedback,
            'shouldBanUser' => $this->shouldBanUser,
            'banReason' => $this->banReason,
            'banDays' => 30,
        ]);

        app(HandleReportAction::class)
            ->exec(
                data: $data,
                reviewer: auth()->user(),
            );

        $this->reset(['activeReport', 'adminFeedback', 'shouldBanUser', 'banReason']);
        $this->dispatch('close-modal', name: 'report-response-modal');
        Toaster::success('Decisão aplicada com sucesso.');
    }

    #[Computed]
    public function reports()
    {
        $filterData = ReportFilterData::from($this->filters->all());

        $query = Report::query()
            ->with(['reporter.profile', 'reviewer.profile', 'reportable'])
            ->when($this->tab === 'moderation', function ($q) {
                $q->whereIn('reason', [
                    ReportReasonEnum::SPAM,
                    ReportReasonEnum::ABUSE,
                    ReportReasonEnum::HARASSMENT,
                    ReportReasonEnum::PLAGIARISM,
                    ReportReasonEnum::INAPPROPRIATE,
                ]);
            })
            ->when($this->tab === 'feedback', function ($q) {
                $q->whereIn('reason', [
                    ReportReasonEnum::PRAISE,
                    ReportReasonEnum::SUGGESTION,
                    ReportReasonEnum::BUG,
                ]);
            })
            ->when($filterData->search, function ($query, $search) {
                $query->whereHas('reporter', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($filterData->status, fn ($q) => $q->where('status', $filterData->status))
            ->when($filterData->reason, fn ($q) => $q->where('reason', $filterData->reason))
            ->orderBy($filterData->sort, $filterData->direction);

        return $query->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.reports.report-index');
    }
}
