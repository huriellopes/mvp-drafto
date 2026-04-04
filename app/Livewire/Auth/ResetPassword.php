<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\Auth\ResetPasswordAction;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Redefinir Senha')]
class ResetPassword extends Component
{
    public string $token;

    #[Validate(['required', 'email'])]
    public string $email = '';

    #[Validate(['required', 'string', 'min:8', 'confirmed'])]
    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate();

        app(ResetPasswordAction::class)->exec([
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        session()->flash('success', 'Sua senha foi redefinida com sucesso.');

        $this->redirectRoute('login', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }
}
