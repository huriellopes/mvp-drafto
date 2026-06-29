<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Settings;

use App\Actions\Users\DeleteMyAccountAction;
use App\Actions\Users\ExportUserDataAction;
use App\Actions\Users\UpgradeToWriterAction;
use App\Enums\RoleEnum;
use App\Livewire\Forms\Settings\UserSettingsForm;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

#[Layout('layouts.app', ['heading' => 'Configurações', 'subheading' => 'Gerencie os dados da sua conta e segurança'])]
#[Title('Configurações de Conta')]
class AccountSettings extends Component
{
    public UserSettingsForm $form;

    public string $deletePassword = '';

    public function mount(): void
    {
        $this->form->setUser(auth()->user());
    }

    public function openBecomeWriterModal(): void
    {
        $this->dispatch('open-modal', name: 'confirm-become-writer');
    }

    /**
     * @throws Throwable
     */
    public function becomeWriter(): void
    {
        $user = auth()->user();

        if ($user->role !== RoleEnum::READER) {
            return;
        }

        resolve(UpgradeToWriterAction::class)
            ->exec(
                user: $user,
            );

        $this->dispatch('close-modal', name: 'confirm-become-writer');

        Toaster::success('Parabéns! Agora você é um Escritor no Drafto.');

        $this->redirect(route('dashboard.account'), navigate: true);
    }

    public function openDeleteAccountModal(): void
    {
        // Limpa estado anterior para não reabrir o modal com senha/erro antigos.
        $this->reset('deletePassword');
        $this->resetErrorBag('deletePassword');

        $this->dispatch('open-modal', name: 'confirm-delete-account');
    }

    /**
     * @throws Throwable
     */
    public function deleteAccount(): void
    {
        // Exige a confirmação da senha atual antes de excluir a conta.
        $this->validate(
            ['deletePassword' => ['required', 'current_password']],
            [
                'deletePassword.required' => 'Informe sua senha para confirmar a exclusão.',
                'deletePassword.current_password' => 'A senha informada está incorreta.',
            ],
        );

        $user = auth()->user();

        auth()->logout();

        resolve(DeleteMyAccountAction::class)->exec($user);

        session()->invalidate();
        session()->regenerateToken();

        Toaster::success('Sua conta foi excluída. Esperamos te ver novamente em breve.');

        $this->redirectRoute('home', navigate: true);
    }

    public function exportData(): StreamedResponse
    {
        $data = resolve(ExportUserDataAction::class)->exec(auth()->user());

        $filename = 'drafto-meus-dados-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(
            function () use ($data) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            },
            $filename,
            ['Content-Type' => 'application/json'],
        );
    }

    public function save(): void
    {
        $this->form->update();

        Toaster::success('Configurações atualizadas com sucesso!');

        if (auth()->user()->email_verified_at === null) {
            Toaster::info('Um novo link de verificação foi enviado para seu e-notifications.');
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.settings.account-settings');
    }
}
