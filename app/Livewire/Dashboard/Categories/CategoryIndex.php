<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Categories;

use App\Livewire\Forms\Dashboard\CategoryForm;
use App\Models\PostCategory;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Minhas Categorias', 'subheading' => 'Organize seus textos em colunas ou nichos específicos'])]
#[Title('Gestão de Categorias')]
class CategoryIndex extends Component
{
    use WithPagination;

    public CategoryForm $form;

    public $search = '';

    public ?int $categoryIdBeingDeleted = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function edit(?int $id = null): void
    {
        $this->resetValidation();
        $this->form->editingCategoryId = $id;

        if ($id) {
            $category = PostCategory::query()->where('user_id', auth()->id())->findOrFail($id);
            $this->form->setCategory($category);
        } else {
            $this->form->reset();
        }

        $this->dispatch('open-modal', name: 'category-modal');
    }

    public function save(): void
    {
        $isEditing = (bool) $this->form->editingCategoryId;
        $this->form->save();

        $this->dispatch('close-modal', name: 'category-modal');
        Toaster::success($isEditing ? 'Categoria atualizada!' : 'Categoria criada com sucesso!');
    }

    public function confirmDelete(int $id): void
    {
        $this->categoryIdToDelete = $id;
        $this->dispatch('open-modal', name: 'confirm-delete-category');
    }

    public function delete(): void
    {
        $category = PostCategory::query()
            ->where('user_id', auth()->id())
            ->withCount('posts')
            ->findOrFail($this->categoryIdToDelete);

        if ($category->posts_count > 0) {
            Toaster::error('Não é possível excluir uma categoria que possui textos vinculados.');
            $this->dispatch('close-modal', name: 'confirm-delete-category');

            return;
        }

        $category->delete();
        $this->categoryIdToDelete = null;

        $this->dispatch('close-modal', name: 'confirm-delete-category');
        Toaster::success('Categoria removida.');
    }

    #[Computed]
    public function categories()
    {
        return PostCategory::query()
            ->where('user_id', auth()->id())
            ->withCount('posts')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(9);
    }

    public function render(): View
    {
        return view('livewire.dashboard.categories.category-index');
    }
}
