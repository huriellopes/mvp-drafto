<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', [
    'heading' => 'Dashboard',
    'subheading' => 'Visão geral da sua produção e atividade',
])]
#[Title('Dashboard')]
class Index extends Component
{
    public function mount()
    {
        // 
    }

    #[Computed]
    public function user(): User
    {
        return Auth::user() ?? abort(403);
    }

    public function render(): View
    {
        return view('livewire.dashboard.index');
    }
}
