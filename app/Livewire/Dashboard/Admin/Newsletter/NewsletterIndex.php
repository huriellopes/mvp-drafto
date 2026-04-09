<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Admin\Newsletter;

use App\Actions\Newsletter\DeleteSubscriberAction;
use App\Actions\Newsletter\ListSubscribersAction;
use App\DTOs\NewsletterFilterData;
use App\Exports\SubscribersExport;
use App\Jobs\SendNewsletterJob;
use App\Livewire\Forms\Admin\NewsletterFilterForm;
use App\Models\NewsletterSubscriber;
use App\Models\PostCategory;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app', ['heading' => 'Newsletter', 'subheading' => 'Gerencie os inscritos e preferências de conteúdo'])]
#[Title('Inscritos Newsletter')]
#[Lazy]
class NewsletterIndex extends Component
{
    use WithPagination;

    public NewsletterFilterForm $filters;

    public ?int $subscriberIdBeingDeleted = null;

    public string $customMessage = '';

    public function sortBy(string $column): void
    {
        $this->filters->direction = ($this->filters->sort === $column && $this->filters->direction === 'asc') ? 'desc' : 'asc';
        $this->filters->sort = $column;
    }

    public function updatedFiltersSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDeletion(int $id): void
    {
        $this->subscriberIdBeingDeleted = $id;
        $this->dispatch('open-modal', name: 'confirm-subscriber-deletion');
    }

    public function delete(DeleteSubscriberAction $action): void
    {
        if (!$this->subscriberIdBeingDeleted) {
            return;
        }

        $subscriber = NewsletterSubscriber::findOrFail($this->subscriberIdBeingDeleted);
        $action->exec($subscriber);

        $this->subscriberIdBeingDeleted = null;
        Toaster::success('Inscrito removido com sucesso.');
    }

    public function export(): BinaryFileResponse
    {
        $filters = NewsletterFilterData::from($this->filters->all());

        return (new SubscribersExport($filters))
            ->download('inscritos-newsletter-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function sendManualNewsletter(): void
    {
        $this->validate(['customMessage' => 'required|min:10']);

        $message = $this->customMessage;

        NewsletterSubscriber::chunk(100, function ($subscribers) use ($message) {
            foreach ($subscribers as $subscriber) {
                SendNewsletterJob::dispatch(
                    subscriber: $subscriber,
                    posts: [],
                    categoryName: 'Informativo',
                    customMessage: $message,
                );
            }
        });

        $this->reset('customMessage');
        $this->dispatch('close-modal', name: 'manual-newsletter-modal');
        Toaster::success('Disparo manual iniciado em segundo plano!');
    }

    #[Computed]
    public function categories()
    {
        return PostCategory::query()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function subscribers()
    {
        return app(ListSubscribersAction::class)
            ->exec(
                filters: NewsletterFilterData::from($this->filters->all()),
            );
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin.newsletter.newsletter-index');
    }
}
