<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\MagicLinkForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.auth')]
#[Title('Entrar com link mágico')]
class MagicLinkRequest extends Component
{
    public MagicLinkForm $form;

    public bool $sent = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute('dashboard.index', navigate: true);
        }
    }

    public function sendLink(): void
    {
        $this->form->send();

        $this->sent = true;

        Toaster::success(__('auth.magic_link.sent_toast'));
    }

    public function render(): View
    {
        return view('livewire.auth.magic-link-request');
    }
}
