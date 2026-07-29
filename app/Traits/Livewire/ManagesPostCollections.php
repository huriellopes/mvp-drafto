<?php

declare(strict_types=1);

namespace App\Traits\Livewire;

use App\Actions\PostCollections\TogglePostInCollectionAction;
use App\Models\Post;
use App\Models\PostCollection;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Masmerise\Toaster\Toaster;

/**
 * Ação rápida "adicionar a coleção" reutilizada nas listagens de posts
 * (Meus Conteúdos e Rascunhos). O componente que usa o trait só precisa
 * incluir o partial do modal e disparar openCollections($postId).
 */
trait ManagesPostCollections
{
    public ?int $postIdForCollections = null;

    public function openCollections(int $postId): void
    {
        // Garante que o post pertence ao usuário autenticado.
        Post::query()
            ->where('user_id', auth()->id())
            ->findOrFail($postId);

        $this->postIdForCollections = $postId;
        $this->dispatch('open-modal', name: 'post-collections-quick');
    }

    public function toggleCollectionForPost(int $collectionId): void
    {
        if (!$this->postIdForCollections) {
            return;
        }

        $post = Post::query()
            ->where('user_id', auth()->id())
            ->findOrFail($this->postIdForCollections);

        $collection = PostCollection::query()
            ->where('user_id', auth()->id())
            ->findOrFail($collectionId);

        $attached = resolve(TogglePostInCollectionAction::class)->exec($collection, $post);

        unset($this->quickCollections);

        Toaster::success($attached ? 'Obra adicionada à coleção.' : 'Obra removida da coleção.');
    }

    /**
     * @return Collection<int, PostCollection>
     */
    #[Computed]
    public function quickCollections(): Collection
    {
        if (!$this->postIdForCollections) {
            return new Collection;
        }

        $post = Post::query()
            ->where('user_id', auth()->id())
            ->with('collections:id')
            ->find($this->postIdForCollections);

        $currentIds = $post ? $post->collections->pluck('id')->all() : [];

        return PostCollection::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get()
            ->map(function (PostCollection $collection) use ($currentIds): PostCollection {
                $collection->setAttribute('in_collection', in_array($collection->id, $currentIds, true));

                return $collection;
            });
    }
}
