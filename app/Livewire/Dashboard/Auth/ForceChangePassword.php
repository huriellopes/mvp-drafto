<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.auth')]
#[Title('Alterar Senha')]
class ForceChangePassword extends Component
{
    public string $password = '';

    public string $password_confirmation = '';

    public function changePassword(): void
    {
        $this->validate();

        auth()->user()->update([
            'password' => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        Toaster::success('Sua senha foi alterada com sucesso! Agora você pode acessar o dashboard.');

        $this->redirect(route('dashboard.index'));
    }

    public function render()
    {
        return view('livewire.dashboard.auth.force-change-password');
    }

    protected function rules(): array
    {
        return [
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()->symbols()],
        ];
    }
}
