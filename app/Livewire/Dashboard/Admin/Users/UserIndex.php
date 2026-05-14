<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Users;

use App\Actions\Auth\ImpersonateUserAction;
use App\Actions\Modules\ToggleUserModuleAction;
use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\ListUsersAction;
use App\Actions\Users\ToggleUserStatusAction;
use App\DTOs\UserFilterData;
use App\Enums\UserStatusEnum;
use App\Exports\UsersExport;
use App\Livewire\Forms\Admin\UserForm;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

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

    public ?User $selectedUserForImpersonation = null;

    public ?User $selectedUserForModules = null;

    public function confirmImpersonation(User $user): void
    {
        $this->selectedUserForImpersonation = $user;
        $this->dispatch('open-modal', name: 'confirm-impersonation');
    }

    public function impersonate(): void
    {
        if (!$this->selectedUserForImpersonation) {
            return;
        }

        if (app(ImpersonateUserAction::class)->exec($this->selectedUserForImpersonation)) {
            Toaster::success("Agora você está logado como {$this->selectedUserForImpersonation->name}");
            $this->redirectRoute('dashboard.index', navigate: true);
        } else {
            Toaster::error('Não foi possível realizar a impersonação.');
        }

        $this->selectedUserForImpersonation = null;
    }

    public function manageModules(User $user): void
    {
        $this->selectedUserForModules = $user;
        $this->dispatch('open-modal', name: 'user-modules-modal');
    }

    public function toggleUserModule(int $moduleId): void
    {
        if (!$this->selectedUserForModules) {
            return;
        }

        $module = Module::find($moduleId);

        if (!$module) {
            return;
        }

        $action = new ToggleUserModuleAction();
        $action->exec($this->selectedUserForModules, $module);

        $this->selectedUserForModules->load('modules');
        Toaster::success("Permissão do módulo {$module->name} alterada.");
    }

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
    public function allModules()
    {
        return Module::all();
    }

    #[Computed]
    public function users()
    {
        $paginator = app(ListUsersAction::class)->exec(
            filters: UserFilterData::from([
                'search' => $this->search,
                'role' => $this->role,
                'sort' => $this->sort,
                'direction' => $this->direction,
                'per_page' => 10,
            ]),
        );

        // Remove o usuário logado da listagem administrativa para evitar auto-edição acidental
        $paginator->setCollection(
            $paginator->getCollection()->reject(fn ($user) => $user->id === auth()->id()),
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

        app(ToggleUserStatusAction::class)->exec(
            user: $user,
            targetStatus: $targetStatus,
        );

        $this->clearUserCache();
        Toaster::success('Status atualizado com sucesso.');
    }

    public function toggleVerification(User $user): void
    {
        $user->profile->update([
            'is_verified' => !$user->profile->is_verified,
        ]);

        $this->clearUserCache();
        Toaster::success($user->profile->is_verified ? 'Selo de Verificado concedido!' : 'Selo de Verificado removido.');
    }

    public function confirmUserDeletion(int $userId): void
    {
        $this->userIdBeingDeleted = $userId;
        $this->dispatch('open-modal', name: 'confirm-user-deletion');
    }

    public function delete(): void
    {
        if (!$this->userIdBeingDeleted) {
            return;
        }

        $user = User::find($this->userIdBeingDeleted);

        if ($user && app(DeleteUserAction::class)->exec($user)) {
            $this->clearUserCache();
            Toaster::success('Usuário removido com sucesso.');
        }

        $this->userIdBeingDeleted = null;
        $this->dispatch('close-modal', name: 'confirm-user-deletion');
    }

    /**
     * @throws Throwable
     */
    public function save(): void
    {
        $this->form->save();
        $this->clearUserCache();
        $this->dispatch('close-modal', name: 'user-form-modal');
        $this->form->reset();
    }

    public function export(): BinaryFileResponse
    {
        $filters = UserFilterData::from([
            'search' => $this->search,
            'role' => $this->role,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ]);

        return (new UsersExport($filters))
            ->download('usuarios-drafto-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.users.user-index');
    }

    private function clearUserCache(): void
    {
        Cache::increment('users_cache_version');
    }
}
