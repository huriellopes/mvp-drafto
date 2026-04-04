<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\Auth\SendPasswordResetLinkAction;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Recuperar senha')]
class ForgotPassword extends Component
{
    #[Validate(['required', 'email'])]
    public string $email = '';

    public function sendResetLink(): void
    {
        $this->validate();

        app(SendPasswordResetLinkAction::class)
            ->exec($this->email);

        session()->flash('success', 'Link de recuperação enviado para seu e-mail.');
        $this->reset('email');
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}
