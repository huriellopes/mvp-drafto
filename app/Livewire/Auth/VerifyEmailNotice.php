<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Verifique seu e-mail')]
class VerifyEmailNotice extends Component
{
    public function render(): View
    {
        return view('livewire.auth.verify-email-notice');
    }
}
