<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\LoginForm;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Login extends Component
{
    public LoginForm $form;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute('dashboard.index', navigate: true);
        }
    }

    /**
     * @throws AuthenticationException
     */
    public function login(): void
    {
        $this->form->authenticate();

        $this->redirectRoute('dashboard.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
