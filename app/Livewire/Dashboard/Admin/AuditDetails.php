<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin;

use Livewire\Attributes\On;
use Livewire\Component;
use OwenIt\Auditing\Models\Audit;

class AuditDetails extends Component
{
    public ?Audit $audit = null;

    public bool $showingModal = false;

    #[On('open-audit-details')]
    public function show(int $auditId): void
    {
        $this->audit = Audit::with('user')->find($auditId);
        $this->dispatch('open-modal', name: 'audit-details-modal');
    }

    public function closeModal(): void
    {
        $this->dispatch('close-modal', name: 'audit-details-modal');
        $this->audit = null;
    }

    public function render()
    {
        // Segurança: ver comentário em SiteAnalytics::render() — mesma
        // proteção necessária contra o bypass estrutural do Livewire.
        $this->authorize('admin');

        return view('livewire.dashboard.admin.audit-details');
    }
}
