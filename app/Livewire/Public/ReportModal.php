<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Actions\Public\StoreReportAction;
use App\DTOs\StoreReportData;
use App\Livewire\Forms\Public\ReportForm;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ReportModal extends Component
{
    public ReportForm $form;

    public bool $show = false;

    #[On('openReportModal')]
    public function open($type, $id)
    {
        if (auth()->guest()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $normalizedType = mb_strtolower(class_basename($type));

        $allowedTypes = ['post', 'comment', 'user'];

        if (!in_array($normalizedType, $allowedTypes, true)) {
            return;
        }

        $this->form->setTarget($normalizedType, $id);
        $this->show = true;
    }

    public function submit()
    {
        $this->validate();

        app(StoreReportAction::class)->exec(
            StoreReportData::from($this->form->getData()),
        );

        $this->reset(['show']);
        $this->show = false;
        $this->form->reset();

        Toaster::success('Enviado com sucesso! Obrigado pelo feedback.');
    }

    public function render()
    {
        return view('livewire.public.report-modal');
    }
}
