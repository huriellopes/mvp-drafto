<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\PostViews;

use App\Actions\PostViews\ListPostViewsAction;
use App\DTOs\PostViewFilterData;
use App\Exports\PostViewsExport;
use App\Jobs\ExportDataJob;
use App\Livewire\Traits\WithBackgroundExport;
use App\Models\PostView;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Analytics', 'subheading' => 'Monitoramento de tráfego de posts'])]
#[Title('Visualizações de Posts')]
class PostViewIndex extends Component
{
    use WithBackgroundExport, WithPagination;

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

    public function export(): void
    {
        $filters = PostViewFilterData::from($this->all());
        $fileName = 'analytics_posts_' . now()->format('Ymd_His') . '.xlsx';
        $this->generatedPath = 'temp/' . $fileName;

        dispatch(new ExportDataJob(PostViewsExport::class, ['filters' => $filters], $fileName));

        Toaster::info('O relatório de visualizações está sendo gerado...');
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
        return resolve(ListPostViewsAction::class)
            ->exec(
                PostViewFilterData::from($this->all()),
            );
    }

    public function render(): View
    {
        $this->authorize('viewAny', PostView::class);

        return view('livewire.dashboard.admin.post-views.post-view-index');
    }
}
