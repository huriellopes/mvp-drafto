<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\ResetPasswordForm;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.auth')]
#[Title('Redefinir Senha')]
class ResetPassword extends Component
{
    public ResetPasswordForm $form;

    public function mount(string $token): void
    {
        $this->form->token = $token;
        $this->form->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->form->submit();

        Toaster::success('Sua senha foi redefinida com sucesso.');
        $this->redirectRoute('login', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }
}
