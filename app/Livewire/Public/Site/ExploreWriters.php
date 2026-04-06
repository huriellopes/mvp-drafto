<?php

declare(strict_types=1);

namespace App\Livewire\Public\Site;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Url, Computed};

class ExploreWriters extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public function updatedSearch() { $this->resetPage(); }

    public function render()
    {
        $writers = User::query()
            ->with(['profile'])
            ->whereHas('profile')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhereHas('profile', fn($p) => $p->where('username', 'like', "%{$this->search}%")))
            ->withCount('publishedPosts')
            ->orderBy('published_posts_count', 'desc')
            ->paginate(16);

        return view('livewire.public.site.explore-writers', ['writers' => $writers])
            ->layout('layouts.site');
    }
}
