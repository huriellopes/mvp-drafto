<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin;

use App\Actions\Admin\ListShortLinksAction;
use App\DTOs\ShortLinkFilterData;
use App\Exports\ShortLinksExport;
use App\Jobs\ExportDataJob;
use App\Livewire\Traits\WithBackgroundExport;
use App\Models\ShortLink;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Gestão de Links', 'subheading' => 'Monitore e gerencie os links encurtados da plataforma'])]
#[Title('Links Encurtados')]
final class ShortLinkIndex extends Component
{
    use WithBackgroundExport, WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = 'created_at';

    #[Url(history: true)]
    public string $direction = 'desc';

    public ?int $linkIdBeingDeleted = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
        $this->resetPage();
    }

    public function confirmDeletion(int $id): void
    {
        $this->linkIdBeingDeleted = $id;
        $this->dispatch('open-modal', name: 'confirm-link-deletion');
    }

    public function delete(): void
    {
        if (!$this->linkIdBeingDeleted) {
            return;
        }

        $link = ShortLink::find($this->linkIdBeingDeleted);

        if ($link) {
            $link->delete();
            Toaster::success('Link encurtado removido com sucesso.');
        }

        $this->linkIdBeingDeleted = null;
        $this->dispatch('close-modal', name: 'confirm-link-deletion');
    }

    public function export(): void
    {
        $filters = new ShortLinkFilterData(
            search: $this->search,
            sort: $this->sort,
            direction: $this->direction,
        );

        $fileName = 'links-encurtados-drafto-' . now()->format('Y-m-d-His') . '.xlsx';
        $this->generatedPath = 'temp/' . $fileName;

        dispatch(new ExportDataJob(ShortLinksExport::class, ['filters' => $filters], $fileName));

        Toaster::info('A exportação dos links foi iniciada...');
    }

    #[Computed]
    public function globalStats(): array
    {
        return [
            'total_links' => ShortLink::count(),
            'total_clicks' => ShortLink::sum('clicks'),
        ];
    }

    #[Computed]
    public function links()
    {
        return resolve(ListShortLinksAction::class)->exec(
            filters: new ShortLinkFilterData(
                search: $this->search,
                sort: $this->sort,
                direction: $this->direction,
            ),
        );
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.short-link-index');
    }
}
