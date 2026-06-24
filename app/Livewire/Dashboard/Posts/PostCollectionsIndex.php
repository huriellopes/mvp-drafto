<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Posts;

use App\Actions\PostCollections\DeletePostCollectionAction;
use App\Actions\PostCollections\TogglePostInCollectionAction;
use App\Livewire\Forms\Dashboard\Posts\PostCollectionForm;
use App\Models\Post;
use App\Models\PostCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Coleções', 'subheading' => 'Organize suas obras em séries, ensinamentos e temas'])]
#[Title('Minhas Coleções')]
class PostCollectionsIndex extends Component
{
    public PostCollectionForm $form;

    #[Url(as: 'colecao', history: true)]
    public ?int $collectionId = null;

    public ?int $collectionIdBeingDeleted = null;

    public string $postSearch = '';

    public function mount(): void
    {
        $this->authorize('viewAny', PostCollection::class);
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

        app(DeletePostCollectionAction::class)->exec($collection);

        if ($this->collectionId === $collection->id) {
            $this->collectionId = null;
        }

        $this->reset('collectionIdBeingDeleted');
        Toaster::success('Coleção removida. Suas obras foram mantidas.');
    }

    public function select(int $id): void
    {
        $this->collectionId = $id;
        $this->postSearch = '';
    }

    public function clearSelection(): void
    {
        $this->collectionId = null;
    }

    public function togglePost(int $postId): void
    {
        $collection = $this->activeCollection;

        if (!$collection) {
            return;
        }

        $this->authorize('update', $collection);

        $post = Post::query()
            ->where('user_id', auth()->id())
            ->findOrFail($postId);

        $attached = app(TogglePostInCollectionAction::class)->exec($collection, $post);

        unset($this->activeCollection, $this->attachablePosts);

        Toaster::success($attached ? 'Obra adicionada à coleção.' : 'Obra removida da coleção.');
    }

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
        if (!$this->collectionId) {
            return null;
        }

        return auth()->user()
            ->postCollections()
            ->with(['posts' => fn ($q) => $q->latest('post_collection_post.created_at')])
            ->find($this->collectionId);
    }

    /**
     * @return SupportCollection<int, Post>
     */
    #[Computed]
    public function attachablePosts(): SupportCollection
    {
        $collection = $this->activeCollection;

        if (!$collection) {
            return new SupportCollection();
        }

        return Post::query()
            ->where('user_id', auth()->id())
            ->when($this->postSearch, fn (Builder $q) => $q->where('title', 'like', "%{$this->postSearch}%"))
            ->latest()
            ->limit(15)
            ->get(['id', 'title', 'status'])
            ->map(function (Post $post) use ($collection): Post {
                $post->setAttribute('in_collection', $collection->posts->contains($post->id));

                return $post;
            });
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
