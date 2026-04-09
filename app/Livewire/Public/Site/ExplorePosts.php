<?php

declare(strict_types=1);

namespace App\Livewire\Public\Site;

use App\Actions\Public\ListPublicPostsAction;
use App\DTOs\Public\PostFilterData;
use App\Models\PostCategory;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RalphJSmit\Laravel\SEO\Support\SEOData;

#[Layout('layouts.site', [
    'seo' => new SEOData(
        title: 'Explorar Biblioteca | Drafto',
        description: 'Navegue por centenas de artigos e posts sobre tecnologia, arte e cultura.',
    ),
])]
#[Lazy]
class ExplorePosts extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $category = '';

    #[Url(history: true)]
    public string $tag = '';

    #[Url(history: true)]
    public string $type = '';

    #[Url(history: true)]
    public string $sort = 'latest'; // latest, popular, commented

    /**
     * Reseta a paginação automaticamente ao mudar qualquer filtro.
     */
    public function updated($property): void
    {
        if (in_array($property, ['search', 'category', 'tag', 'sort', 'type'], true)) {
            $this->resetPage();
        }
    }

    public function placeholder(): View
    {
        return view('livewire.public.site.placeholders.explore-posts');
    }

    #[Computed]
    public function categories()
    {
        return PostCategory::withCount('posts')
            ->orderBy('posts_count', 'desc') // Sênior: Mostra as mais relevantes primeiro
            ->take(10)
            ->get();
    }

    #[Computed]
    public function tags()
    {
        return Tag::query()
            ->whereHas('posts')
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->take(15)
            ->get();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'tag', 'sort', 'type']);
        $this->resetPage();
    }

    public function render(): View
    {
        $posts = app(ListPublicPostsAction::class)->exec(
            PostFilterData::from([
                'search' => $this->search,
                'category' => $this->category,
                'tag' => $this->tag,
                'type' => $this->type,
                'sort' => $this->sort,
                'perPage' => 10,
            ]),
        );

        return view('livewire.public.site.explore-posts', ['posts' => $posts]);
    }
}
