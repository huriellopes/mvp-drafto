<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Support;

use App\Actions\Support\UpdateSupportAction;
use App\DTOs\SupportData;
use App\Enums\SupportStatusEnum;
use App\Models\Support;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app')]
#[Title('Gestão de Suporte')]
final class SupportIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public ?int $selectedSupportId = null;
    public string $adminResponse = '';
    public string $newStatus = '';

    protected $queryString = ['search', 'status'];

    public function selectSupport(int $id): void
    {
        $support = Support::findOrFail($id);
        $this->selectedSupportId = $id;
        $this->adminResponse = $support->admin_response ?? '';
        $this->newStatus = $support->status->value;

        $this->dispatch('open-modal', name: 'respond-support');
    }

    public function saveResponse(): void
    {
        if (!$this->selectedSupportId) return;

        $support = Support::findOrFail($this->selectedSupportId);

        app(UpdateSupportAction::class)->exec(
            auth()->user(),
            $support,
            SupportData::from([
                'subject' => $support->subject,
                'message' => $support->message,
                'admin_response' => $this->adminResponse,
                'status' => $this->newStatus,
            ])
        );

        Toaster::success('Resposta enviada e status atualizado!');

        $this->dispatch('close-modal', name: 'respond-support');
        $this->reset(['adminResponse', 'newStatus']);
        $this->selectedSupportId = null;
    }

    public function render(): View
    {
        $this->authorize('admin');

        $supports = Support::query()
            ->with('user')
            ->when($this->search, fn($q) => $q->where('subject', 'like', "%{$this->search}%")
                ->orWhereHas('user', fn($qu) => $qu->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.dashboard.admin.support.support-index', [
            'supports' => $supports
        ]);
    }
}
