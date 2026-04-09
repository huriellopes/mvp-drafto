<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\RegisterForm;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.auth')]
class Register extends Component
{
    public RegisterForm $form;

    /**
     * @throws Throwable
     */
    public function register(): void
    {
        $this->form->store();

        session()->flash('success', __('auth.status.account_created'));

        $this->redirectRoute('dashboard.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
