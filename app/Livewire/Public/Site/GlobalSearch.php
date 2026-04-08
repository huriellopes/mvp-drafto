<?php

namespace App\Livewire\Public\Site;

use App\Actions\Public\GlobalSearchAction;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $search = '';
    public bool $isOpen = false;

    public function updatedSearch(): void
    {
        // A lógica de busca é disparada automaticamente pelo debounce no Blade
    }

    public function render()
    {
        $results = app(GlobalSearchAction::class)->exec($this->search);

        return view('livewire.public.site.global-search', [
            'posts' => $results['posts'],
            'authors' => $results['authors'],
        ]);
    }
}
