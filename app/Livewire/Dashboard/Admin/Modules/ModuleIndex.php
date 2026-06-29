<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Modules;

use App\Actions\Admin\ListModulesAction;
use App\DTOs\ModuleFilterData;
use App\Livewire\Forms\Admin\ModuleForm;
use App\Models\Module;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Módulos', 'subheading' => 'Gerencie as funcionalidades ativas da plataforma'])]
#[Title('Módulos do Sistema')]
#[Lazy]
class ModuleIndex extends Component
{
    use WithPagination;

    public ModuleForm $form;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sortField = 'name';

    #[Url(history: true)]
    public string $sortDirection = 'asc';

    #[Url(history: true)]
    public mixed $perPage = 6;

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleModule(Module $module): void
    {
        $this->form->setModule($module);
        $this->form->is_enabled = !$module->is_enabled;
        $this->form->update();

        $status = $this->form->is_enabled ? 'ativado' : 'desativado';
        Toaster::success("Módulo {$module->name} {$status} com sucesso!");
    }

    #[Computed]
    public function modules()
    {
        $limit = $this->perPage === 'all' ? Module::query()->count() : (int) $this->perPage;

        $limit = $limit > 0 ? $limit : 10;

        return resolve(ListModulesAction::class)->exec(
            dto: new ModuleFilterData(
                search: $this->search,
                sortField: 'name',
                sortDirection: $this->sortDirection,
                perPage: $limit,
            ),
        );
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.modules.module-index');
    }
}
