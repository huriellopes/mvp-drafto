<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Tags;

use App\Livewire\Forms\Dashboard\TagForm;
use App\Models\Tag;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Minhas Tags', 'subheading' => 'Gerencie suas etiquetas personalizadas e visualize as globais.'])]
#[Title('Gestão de Tags')]
class TagsIndex extends Component
{
    use WithPagination;

    public TagForm $form;

    public string $search = '';

    public ?int $tagIdToDelete = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function edit(?int $id = null): void
    {
        $this->resetValidation();
        $this->form->editingTagId = $id;

        if ($id) {
            // Apenas tags do usuário podem ser editadas aqui
            $tag = Tag::query()->where('user_id', auth()->id())->findOrFail($id);
            $this->form->setTag($tag);
        } else {
            $this->form->reset();
        }

        $this->dispatch('open-modal', name: 'tag-modal');
    }

    public function save(): void
    {
        $isEditing = (bool) $this->form->editingTagId;
        $this->form->save();

        $this->dispatch('close-modal', name: 'tag-modal');
        Toaster::success($isEditing ? 'Tag atualizada!' : 'Tag criada com sucesso!');
    }

    public function confirmDelete(int $id): void
    {
        $this->tagIdToDelete = $id;
        $this->dispatch('open-modal', name: 'confirm-delete-tag');
    }

    public function delete(): void
    {
        $tag = Tag::query()
            ->where('user_id', auth()->id())
            ->withCount('posts')
            ->findOrFail($this->tagIdToDelete);

        if ($tag->posts_count > 0) {
            Toaster::error('Não é possível excluir uma tag que possui textos vinculados.');
            $this->dispatch('close-modal', name: 'confirm-delete-tag');

            return;
        }

        $tag->delete();
        $this->tagIdToDelete = null;

        $this->dispatch('close-modal', name: 'confirm-delete-tag');
        Toaster::success('Tag removida.');
    }

    #[Computed]
    public function tags()
    {
        return Tag::query()
            ->where(function ($query) {
                $query->whereNull('user_id')
                    ->orWhere('user_id', auth()->id());
            })
            ->withCount('posts')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderByRaw('user_id IS NULL DESC') // Globais primeiro ou depois? Vamos colocar as do usuário primeiro.
            ->orderBy('name', 'asc')
            ->paginate(12);
    }

    public function render(): View
    {
        return view('livewire.dashboard.tags.tags-index');
    }
}
