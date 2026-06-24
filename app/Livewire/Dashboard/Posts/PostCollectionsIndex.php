<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Posts;

use App\Actions\PostCollections\DeletePostCollectionAction;
use App\Enums\PostStatusEnum;
use App\Livewire\Forms\Dashboard\Posts\PostCollectionForm;
use App\Models\Post;
use App\Models\PostCollection;
use App\Traits\Livewire\ManagesPostCollections;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Coleções', 'subheading' => 'Organize suas obras em séries, ensinamentos e temas'])]
#[Title('Minhas Coleções')]
class PostCollectionsIndex extends Component
{
    use ManagesPostCollections;
    use WithPagination;

    public PostCollectionForm $form;

    #[Url(as: 'colecao', history: true)]
    public ?string $collection = null;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $statusFilter = '';

    public ?int $collectionIdBeingDeleted = null;

    public function mount(): void
    {
        $this->authorize('viewAny', PostCollection::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCollection(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function createCollection(): void
    {
        $this->form->store();
        $this->dispatch('close-modal', name: 'post-collection-modal');
        Toaster::success('Coleção criada com sucesso!');
    }

    public function openEditModal(int $id): void
    {
        $collection = $this->ownedCollection($id);
        $this->authorize('update', $collection);
        $this->form->setCollection($collection);
        $this->dispatch('open-modal', name: 'edit-post-collection-modal');
    }

    public function updateCollection(): void
    {
        $this->authorize('update', $this->form->collection);
        $this->form->update();
        $this->dispatch('close-modal', name: 'edit-post-collection-modal');
        Toaster::success('Coleção atualizada com sucesso!');
    }

    public function confirmDelete(int $id): void
    {
        $this->collectionIdBeingDeleted = $id;
        $this->dispatch('open-modal', name: 'confirm-delete-post-collection');
    }

    public function deleteCollection(): void
    {
        if (!$this->collectionIdBeingDeleted) {
            return;
        }

        $collection = $this->ownedCollection($this->collectionIdBeingDeleted);
        $this->authorize('delete', $collection);

        if ($this->collection === $collection->slug) {
            $this->collection = null;
        }

        app(DeletePostCollectionAction::class)->exec($collection);

        $this->reset('collectionIdBeingDeleted');
        Toaster::success('Coleção removida. Suas obras foram mantidas.');
    }

    public function removeFromActiveCollection(int $postId): void
    {
        $active = $this->activeCollection;

        if (!$active) {
            return;
        }

        $this->authorize('update', $active);

        $post = Post::query()
            ->where('user_id', auth()->id())
            ->findOrFail($postId);

        $active->posts()->detach($post->id);

        Toaster::success('Obra removida desta coleção.');
    }

    /**
     * @return Collection<int, PostCollection>
     */
    #[Computed]
    public function collections(): Collection
    {
        return auth()->user()
            ->postCollections()
            ->withCount('posts')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function activeCollection(): ?PostCollection
    {
        if (!$this->collection) {
            return null;
        }

        return auth()->user()
            ->postCollections()
            ->where('slug', $this->collection)
            ->first();
    }

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        $active = $this->activeCollection;

        return Post::query()
            ->where('user_id', auth()->id())
            ->with('category:id,name')
            ->when($active, fn (Builder $q) => $q->whereHas('collections', fn (Builder $c) => $c->whereKey($active->id)))
            ->when($this->search, fn (Builder $q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->statusFilter === 'draft', fn (Builder $q) => $q->where('status', PostStatusEnum::DRAFT))
            ->when($this->statusFilter === 'published', fn (Builder $q) => $q->where('status', PostStatusEnum::PUBLISHED))
            ->latest()
            ->paginate(12);
    }

    public function render(): View
    {
        return view('livewire.dashboard.posts.post-collections-index');
    }

    private function ownedCollection(int $id): PostCollection
    {
        return auth()->user()->postCollections()->findOrFail($id);
    }
}
