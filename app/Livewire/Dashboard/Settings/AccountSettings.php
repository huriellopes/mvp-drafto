<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Settings;

use App\Livewire\Forms\Settings\UserSettingsForm;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Configurações', 'subheading' => 'Gerencie os dados da sua conta e segurança'])]
#[Title('Configurações de Conta')]
class AccountSettings extends Component
{
    public UserSettingsForm $form;

    public function mount(): void
    {
        $this->form->setUser(auth()->user());
    }

    public function save(): void
    {
        $this->form->update();

        Toaster::success('Configurações atualizadas com sucesso!');

        if (auth()->user()->email_verified_at === null) {
            Toaster::info('Um novo link de verificação foi enviado para seu e-mail.');
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.settings.account-settings');
    }
}
