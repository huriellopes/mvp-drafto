<?php

declare(strict_types=1);

namespace App\Livewire\Public\Site;

use App\Actions\Public\ListPublicPostsAction;
use App\Models\{PostCategory, Tag};
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Url, Computed};

class ExplorePosts extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $category = '';

    #[Url(history: true)]
    public string $tag = '';

    public function updatedSearch() { $this->resetPage(); }

    #[Computed]
    public function categories() { return PostCategory::orderBy('name')->get(); }

    #[Computed]
    public function tags() { return Tag::has('posts')->take(20)->get(); }

    public function render()
    {
        $posts = app(ListPublicPostsAction::class)->exec([
            'search' => $this->search,
            'category' => $this->category,
            'tag' => $this->tag
        ]);

        return view('livewire.public.site.explore-posts', ['posts' => $posts])
            ->layout('layouts.site');
    }
}
