<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Support;

use App\Livewire\Forms\Support\SupportForm;
use App\Models\Support;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

final class SupportPage extends Component
{
    use WithPagination;

    public SupportForm $form;

    public ?int $selectedTicketId = null;

    public function selectTicket(int $id): void
    {
        $this->selectedTicketId = $id;
        $this->dispatch('open-modal', name: 'view-response');
    }

    public function save(): void
    {
        $this->form->store();
        Toaster::success('Sua mensagem foi enviada com sucesso! Retornaremos em breve no seu sino de notificações e e-mail.');
    }

    public function render(): View
    {
        $tickets = Support::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('livewire.dashboard.support.support-page', [
            'tickets' => $tickets,
        ]);
    }
}
