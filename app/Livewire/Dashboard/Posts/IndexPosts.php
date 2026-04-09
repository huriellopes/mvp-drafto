<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Posts;

use App\Actions\Posts\ListPostsAction;
use App\DTOs\PostFiltersData;
use App\Enums\PostStatusEnum;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Publicações', 'subheading' => 'Gerencie todos os seus textos publicados'])]
#[Title('Minhas Publicações')]
class IndexPosts extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $sort = 'created_at';

    #[Url(history: true)]
    public string $direction = 'desc';

    public ?int $postIdBeingDeleted = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sort === $field) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->postIdBeingDeleted = $id;
        $this->dispatch('open-modal', name: 'confirm-post-deletion');
    }

    public function deletePost(): void
    {
        if (!$this->postIdBeingDeleted) {
            return;
        }

        $post = auth()->user()->posts()->findOrFail($this->postIdBeingDeleted);
        $post->delete();

        $this->reset('postIdBeingDeleted');

        Toaster::success('Post deletado com sucesso!');
    }

    #[Computed]
    public function posts()
    {
        return app(ListPostsAction::class)->exec(
            new PostFiltersData(
                search: $this->search,
                status: PostStatusEnum::tryFrom($this->status),
                notStatus: PostStatusEnum::DRAFT,
                sort: $this->sort,
                direction: $this->direction,
                perPage: 10,
            ),
        );
    }

    public function render(): View
    {
        return view('livewire.dashboard.posts.index-posts');
    }
}
