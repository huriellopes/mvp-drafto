<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\ForgotPasswordForm;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.auth')]
#[Title('Recuperar senha')]
class ForgotPassword extends Component
{
    public ForgotPasswordForm $form;

    public bool $sent = false;

    public function sendResetLink(): void
    {
        $this->form->save();

        $this->sent = true;

        Toaster::success('Verifique sua caixa de entrada!');
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}
