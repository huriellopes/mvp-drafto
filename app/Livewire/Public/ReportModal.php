<?php

namespace App\Livewire\Public;

use App\Actions\Public\StoreReportAction;
use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use App\Livewire\Forms\Public\ReportForm;
use App\Models\Report;
use Livewire\Attributes\On;
use Livewire\Component;

class ReportModal extends Component
{
    public ReportForm $form;
    public bool $show = false;

    #[On('openReportModal')]
    public function open($type, $id)
    {
        if (auth()->guest()) return $this->redirect(route('login'), navigate: true);
        $this->form->setTarget($type, $id);
        $this->show = true;
    }

    public function submit()
    {
        $this->validate();

        app(StoreReportAction::class)->exec(
            array_merge($this->form->all(), ['reporter_id' => auth()->id()])
        );
        $this->reset(['show']);
        $this->form->reset();
        $this->dispatch('notify', message: 'Denúncia enviada.');
    }

    public function render() { return view('livewire.public.report-modal'); }
}
