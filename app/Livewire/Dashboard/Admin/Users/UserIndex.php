<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Users;

use App\Actions\Auth\ImpersonateUserAction;
use App\Actions\Engagement\SendReengagementEmailAction;
use App\Actions\Modules\ToggleUserModuleAction;
use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\ListUsersAction;
use App\Actions\Users\ResetUserPasswordAction;
use App\Actions\Users\ToggleUserStatusAction;
use App\DTOs\AdminResetPasswordData;
use App\DTOs\UserFilterData;
use App\Enums\UserStatusEnum;
use App\Exports\UsersExport;
use App\Jobs\ExportDataJob;
use App\Livewire\Forms\Admin\UserForm;
use App\Livewire\Traits\WithBackgroundExport;
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
use Throwable;

#[Layout('layouts.app', ['heading' => 'Gestão de Usuários', 'subheading' => 'Administre as contas da plataforma'])]
#[Title('Usuários')]
#[Lazy]
class UserIndex extends Component
{
    use WithBackgroundExport, WithPagination;

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

    public ?User $selectedUserForPasswordReset = null;

    public ?User $selectedUserForReengagement = null;

    public string $defaultPassword = 'Drafto@2026';

    public function confirmImpersonation(User $user): void
    {
        $this->selectedUserForImpersonation = $user;
        $this->dispatch('open-modal', name: 'confirm-impersonation');
    }

    public function confirmPasswordReset(User $user): void
    {
        $this->selectedUserForPasswordReset = $user;
        $this->dispatch('open-modal', name: 'confirm-password-reset');
    }

    public function resetPassword(): void
    {
        if (!$this->selectedUserForPasswordReset) {
            return;
        }

        app(ResetUserPasswordAction::class)->exec(
            new AdminResetPasswordData(
                userId: $this->selectedUserForPasswordReset->id,
                password: $this->defaultPassword,
            ),
        );

        Toaster::success("Senha do usuário {$this->selectedUserForPasswordReset->name} resetada com sucesso.");

        $this->selectedUserForPasswordReset = null;
        $this->dispatch('close-modal', name: 'confirm-password-reset');
    }

    public function confirmReengagement(User $user): void
    {
        $this->selectedUserForReengagement = $user;
        $this->dispatch('open-modal', name: 'confirm-reengagement');
    }

    public function sendReengagement(): void
    {
        $user = $this->selectedUserForReengagement;

        if (!$user) {
            return;
        }

        if (!$user->wants_reengagement_emails) {
            Toaster::warning("{$user->name} optou por não receber lembretes de retorno.");
        } elseif (!$user->isActive()) {
            Toaster::warning("A conta de {$user->name} não está ativa.");
        } elseif ($user->banned_until?->isFuture()) {
            Toaster::warning("{$user->name} está com acesso suspenso.");
        } else {
            // force: envio manual ignora cooldown/faixa e não exige e-mail verificado.
            app(SendReengagementEmailAction::class)->exec($user, force: true);
            Toaster::success("E-mail de retorno enviado para {$user->name}.");
        }

        $this->selectedUserForReengagement = null;
        $this->dispatch('close-modal', name: 'confirm-reengagement');
    }

    public function impersonate(): void
    {
        if (!$this->selectedUserForImpersonation) {
            return;
        }

        if (app(ImpersonateUserAction::class)->exec($this->selectedUserForImpersonation)) {
            Toaster::success("Agora você está logado como {$this->selectedUserForImpersonation->name}");
            $this->redirect(route('dashboard.index'));
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

    public function export(): void
    {
        $filters = UserFilterData::from([
            'search' => $this->search,
            'role' => $this->role,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ]);

        $fileName = 'usuarios-drafto-' . now()->format('Y-m-d-His') . '.xlsx';
        $this->generatedPath = 'temp/' . $fileName;

        ExportDataJob::dispatch(
            UsersExport::class,
            ['filters' => $filters],
            $fileName,
        );

        Toaster::info('A lista de usuários está sendo exportada...');
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
