<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Users;

use App\Enums\UserStatusEnum;
use Illuminate\View\View;
use App\Actions\Users\{ListUsersAction, DeleteUserAction, ToggleUserStatusAction};
use App\Livewire\Forms\Admin\UserForm;
use App\Models\User;
use Livewire\Attributes\{Computed, Layout, Lazy, Title, Url};
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['heading' => 'Gestão de Usuários', 'subheading' => 'Administre as contas da plataforma'])]
#[Title('Usuários')]
#[Lazy]
class UserIndex extends Component
{
    use WithPagination;

    public UserForm $form;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $role = '';

    public bool $isModalOpen = false;

    public ?int $userIdBeingDeleted = null;

    #[Computed]
    public function users()
    {
        return app(ListUsersAction::class)->exec(
            filters: [
                'search' => $this->search,
                'role' => $this->role,
                'per_page' => 10
            ]
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->form->reset();
        $this->dispatch('open-modal', name: 'user-form-modal');
    }

    public function edit(User $user): void
    {
        $this->form->setUser($user);
        $this->dispatch('open-modal', name: 'user-form-modal');
    }

    public function toggleStatus(User $user, ?string $statusValue = null): void
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('notify', message: 'Você não pode alterar seu próprio status.', type: 'error');
            return;
        }

        $targetStatus = $statusValue ? UserStatusEnum::from($statusValue) : null;

        app(ToggleUserStatusAction::class)
            ->exec(
                user: $user,
                targetStatus: $targetStatus
            );

        $this->dispatch('notify', message: 'Status atualizado com sucesso.');
    }

    public function confirmUserDeletion(int $userId): void
    {
        $this->userIdBeingDeleted = $userId;
        $this->dispatch('open-modal', name: 'confirm-user-deletion');
    }

    public function delete(): void
    {
        if (!$this->userIdBeingDeleted) return;

        $user = User::find($this->userIdBeingDeleted);

        if ($user && app(DeleteUserAction::class)->exec($user)) {
            $this->dispatch('notify', message: 'Usuário removido com sucesso.');
        }

        $this->userIdBeingDeleted = null;
        $this->dispatch('close-modal', name: 'confirm-user-deletion');
    }

    /**
     * @throws \Throwable
     */
    public function save(): void
    {
        $this->form->save();
        $this->dispatch('close-modal', name: 'user-form-modal');
        $this->form->reset();
    }

    public function render() : View
    {
        return view('livewire.admin.users.user-index');
    }
}
