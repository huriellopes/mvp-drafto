<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Actions\Auth\LeaveImpersonationAction;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ImpersonationBanner extends Component
{
    public function leave(): void
    {
        if (app(LeaveImpersonationAction::class)->exec()) {
            Toaster::success('Você voltou para sua conta de administrador.');
            $this->redirectRoute('dashboard.admin.users.index', navigate: true);
        }
    }

    public function render()
    {
        if (!Session::has('impersonator_id')) {
            return <<<'HTML'
                <div></div>
            HTML;
        }

        return <<<'HTML'
            <div class="bg-amber-600 text-white py-2 px-4 flex items-center justify-between shadow-lg sticky top-0 z-[110]">
                <div class="flex items-center gap-2 text-sm font-bold uppercase tracking-widest">
                    <x-lucide-user-cog class="h-4 w-4" />
                    <span>Modo Impersonação Ativo: Você está logado como {{ auth()->user()->name }}</span>
                </div>
                <button 
                    wire:click="leave"
                    class="bg-white text-amber-600 px-4 py-1 rounded-lg text-xs font-black uppercase hover:bg-zinc-100 transition"
                >
                    Voltar para Admin
                </button>
            </div>
        HTML;
    }
}
