<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Actions\Auth\LeaveImpersonationAction;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ImpersonationBanner extends Component
{
    public function leave(): void
    {
        if (resolve(LeaveImpersonationAction::class)->exec()) {
            Toaster::success('Você voltou para sua conta de administrador.');
            $this->redirect(route('dashboard.admin.users.index'));
        }
    }

    public function render()
    {
        return view('livewire.dashboard.impersonation-banner');
    }
}
