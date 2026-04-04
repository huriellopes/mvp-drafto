<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\RegisterForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('layouts.auth')]
#[Title('Criar conta')]
class Register extends Component
{
    public RegisterForm $form;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute('dashboard', navigate: true);
        }
    }

    /**
     * @throws Throwable
     */
    public function register(): void
    {
        $this->form->store();

        session()->flash('success', 'Conta criada com sucesso! Bem-vindo à Drafto.');

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
