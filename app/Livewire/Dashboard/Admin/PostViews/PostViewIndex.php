<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\PostViews;

use App\Actions\PostViews\ListPostViewsAction;
use App\DTOs\PostViewFilterData;
use App\Models\PostView;
use App\Exports\PostViewsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\View\View;
use Livewire\Attributes\{Computed, Layout, Title, Url};
use Livewire\{Component, WithPagination};
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Analytics', 'subheading' => 'Monitoramento de tráfego de posts'])]
#[Title('Visualizações de Posts')]
class PostViewIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = 'viewed_at';

    #[Url(history: true)]
    public string $direction = 'desc';

    public ?int $viewIdToDelete = null;

    public function sortBy(string $column): void
    {
        $this->direction = ($this->sort === $column && $this->direction === 'asc') ? 'desc' : 'asc';
        $this->sort = $column;
    }

    public function export()
    {
        $filters = PostViewFilterData::fromArray($this->all());

        return (new PostViewsExport($filters))
            ->download('analytics_posts_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function confirmDelete(int $id): void
    {
        $this->viewIdToDelete = $id;
        $this->dispatch('open-modal', name: 'confirm-delete-view');
    }

    public function delete(): void
    {
        $this->authorize('delete', PostView::class);
        PostView::findOrFail($this->viewIdToDelete)->delete();
        $this->viewIdToDelete = null;
        Toaster::success('Registro removido.');
    }

    #[Computed]
    public function views()
    {
        return app(ListPostViewsAction::class)
            ->exec(
                PostViewFilterData::fromArray($this->all())
        );
    }

    public function render(): View
    {
        $this->authorize('viewAny', PostView::class);

        return view('livewire.dashboard.admin.post-views.post-view-index');
    }
}
