<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Saved;

use App\Actions\Saved\DeleteCollectionAction;
use App\Actions\Saved\ListSavedPostsAction;
use App\Actions\Saved\MoveToCollectionAction;
use App\Actions\Saved\ToggleSavePostAction;
use App\DTOs\SavedPostsFilterData;
use App\Livewire\Forms\Saved\CollectionForm;
use App\Models\Collection;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Biblioteca', 'subheading' => 'Gerencie seus itens salvos e coleções'])]
#[Title('Salvos')]
class SavedIndex extends Component
{
    use WithPagination;

    public CollectionForm $collectionForm;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?int $categoryId = null;

    #[Url(as: 'collection', history: true)]
    public ?string $collection = null;

    public ?int $postIdBeingRemoved = null;

    public bool $isRemovingFromCollection = false;

    public ?int $postIdBeingMoved = null;

    public ?int $targetCollectionId = null;

    public ?int $collectionIdBeingDeleted = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCollection(): void
    {
        $this->resetPage();
    }

    public function createCollection(): void
    {
        $this->collectionForm->store();
        $this->dispatch('close-modal', name: 'new-collection-modal');
        Toaster::success('Coleção criada com sucesso!');
    }

    public function openEditCollectionModal(int $id): void
    {
        // Segurança (IDOR): sem authorize(), qualquer usuário autenticado
        // podia editar a coleção de outro usuário só sabendo o ID — a
        // CollectionPolicy já existia e já checava a posse, só não era
        // chamada aqui.
        $collection = Collection::findOrFail($id);
        $this->authorize('update', $collection);

        $this->collectionForm->setCollection($collection);
        $this->dispatch('open-modal', name: 'edit-collection-modal');
    }

    public function updateCollection(): void
    {
        $this->collectionForm->update();
        $this->dispatch('close-modal', name: 'edit-collection-modal');
        Toaster::success('Coleção atualizada com sucesso!');
    }

    public function confirmDeleteCollection(int $id): void
    {
        $this->collectionIdBeingDeleted = $id;
        $this->dispatch('open-modal', name: 'confirm-delete-collection');
    }

    public function deleteCollection(): void
    {
        if (!$this->collectionIdBeingDeleted) {
            return;
        }

        // Segurança (IDOR): mesma proteção de openEditCollectionModal().
        $collection = Collection::findOrFail($this->collectionIdBeingDeleted);
        $this->authorize('delete', $collection);

        if ($this->collection === $collection->slug) {
            $this->collection = null;
        }

        resolve(DeleteCollectionAction::class)
            ->exec(
                collection: $collection,
            );

        $this->collectionIdBeingDeleted = null;
        Toaster::success('Coleção excluída. Seus posts foram movidos para a lista geral.');
    }

    public function confirmUnsave(int $postId): void
    {
        $this->postIdBeingRemoved = $postId;

        /**
         * Sênior: Verificamos se há um ID de coleção ativo para decidir se apenas
         * removemos da coleção ou se removemos dos salvos completamente.
         * Usar empty() ou o método resolver garante que "" (string vazia da URL) seja tratada como null.
         */
        $this->isRemovingFromCollection = !in_array($this->collection, [null, '', '0'], true);

        $this->dispatch('open-modal', name: 'confirm-unsave-post');
    }

    public function unsave(ToggleSavePostAction $action): void
    {
        if (!$this->postIdBeingRemoved) {
            return;
        }

        $user = auth()->user();
        $post = Post::findOrFail($this->postIdBeingRemoved);

        if ($this->isRemovingFromCollection) {
            // Sênior: Remove apenas o vínculo com a coleção atual
            resolve(MoveToCollectionAction::class)->exec(
                user: $user,
                postId: $post->id,
                collectionId: null,
            );
            Toaster::info('Post movido para a lista geral de salvos.');
        } else {
            // Remove completamente da biblioteca do usuário
            $action->exec($user, $post);
            Toaster::info('Post removido dos itens salvos.');
        }

        // Sênior: Limpamos o estado e invalidamos a propriedade computada para forçar o refresh do grid
        $this->postIdBeingRemoved = null;
        $this->isRemovingFromCollection = false;
        unset($this->savedPosts);
    }

    public function openMoveModal(int $postId, ?int $currentCollectionId): void
    {
        $this->postIdBeingMoved = $postId;
        $this->targetCollectionId = $currentCollectionId;
        $this->dispatch('open-modal', name: 'move-to-collection-modal');
    }

    public function moveToCollection(): void
    {
        if (!$this->postIdBeingMoved) {
            return;
        }

        resolve(MoveToCollectionAction::class)
            ->exec(
                user: auth()->user(),
                postId: $this->postIdBeingMoved,
                collectionId: $this->targetCollectionId ?: null,
            );

        $this->postIdBeingMoved = null;
        $this->targetCollectionId = null;

        $this->dispatch('close-modal', name: 'move-to-collection-modal');
        Toaster::success('Post organizado com sucesso!');
    }

    #[Computed]
    public function collections()
    {
        return Collection::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return PostCategory::query()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function savedPosts()
    {
        return resolve(ListSavedPostsAction::class)->exec(
            user: auth()->user(),
            filters: SavedPostsFilterData::from([
                'search' => $this->search,
                'categoryId' => $this->categoryId,
                'collectionId' => $this->getCurrentCollectionId(),
            ]),
        );
    }

    public function render(): View
    {
        return view('livewire.dashboard.saved.saved-index');
    }

    protected function getCurrentCollectionId(): ?int
    {
        if (!$this->collection) {
            return null;
        }

        return Collection::query()
            ->where('user_id', auth()->id())
            ->where('slug', $this->collection)
            ->value('id');
    }
}
