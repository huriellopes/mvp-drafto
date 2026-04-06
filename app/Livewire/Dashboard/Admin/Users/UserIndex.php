<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Users;

use App\DTOs\UserFilterData;
use App\Enums\UserStatusEnum;
use Illuminate\View\View;
use App\Actions\Users\{ListUsersAction, DeleteUserAction, ToggleUserStatusAction};
use App\Livewire\Forms\Admin\UserForm;
use App\Models\User;
use Livewire\Attributes\{Computed, Layout, Lazy, Title, Url};
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

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

    #[Url(history: true)]
    public string $sort = 'created_at';

    #[Url(history: true)]
    public string $direction = 'desc';

    public bool $isModalOpen = false;
    public ?int $userIdBeingDeleted = null;

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        $paginator = app(ListUsersAction::class)->exec(
            filters: UserFilterData::fromArray([
                'search' => $this->search,
                'role' => $this->role,
                'sort' => $this->sort,
                'direction' => $this->direction,
                'per_page' => 10
            ])
        );

        $paginator->setCollection(
            $paginator->getCollection()->reject(fn ($user) => $user->id === auth()->id())
        );

        return $paginator;
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
            Toaster::warning('Você não pode alterar seu próprio status.');
            return;
        }

        $targetStatus = $statusValue ? UserStatusEnum::from($statusValue) : null;

        app(ToggleUserStatusAction::class)
            ->exec(
                user: $user,
                targetStatus: $targetStatus
            );

        Toaster::success('Status atualizado com sucesso.');
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
            $this->clearUserCache();
            Toaster::success('Usuário removido com sucesso.');
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
        $this->clearUserCache();
        $this->dispatch('close-modal', name: 'user-form-modal');
        $this->form->reset();
    }

    private function clearUserCache(): void
    {
        Cache::increment('users_cache_version');
    }

    public function render() : View
    {
        return view('livewire.dashboard.admin.users.user-index');
    }
}
