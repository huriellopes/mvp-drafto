<?php

declare(strict_types=1);

namespace App\Livewire\Public\Site;

use App\Actions\Public\ListExploreWritersAction;
use App\DTOs\Public\ExploreWritersFilterData;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RalphJSmit\Laravel\SEO\Support\SEOData;

#[Layout('layouts.site', [
    'seo' => new SEOData(
        title: 'Descubra Escritores | Drafto',
        description: 'Conheça as mentes brilhantes que escrevem no Drafto.',
    ),
])]
#[Lazy]
class ExploreWriters extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function placeholder(): View
    {
        return view('livewire.public.site.placeholders.explore-writers');
    }

    #[Computed]
    public function writers()
    {
        return app(ListExploreWritersAction::class)
            ->exec(
                data: new ExploreWritersFilterData(search: $this->search),
            );
    }

    public function render(): View
    {
        return view('livewire.public.site.explore-writers');
    }
}
