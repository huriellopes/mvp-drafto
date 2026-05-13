<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Comments;

use App\Actions\Comments\ListCommentsAction;
use App\DTOs\CommentFilterData;
use App\Livewire\Forms\Dashboard\CommentForm;
use App\Models\Comment;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.app', ['heading' => 'Comentários', 'subheading' => 'Gerencie suas interações e feedbacks nos posts'])]
#[Title('Meus Comentários')]
class CommentIndex extends Component
{
    use WithPagination;

    public CommentForm $form;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $sort = 'created_at';

    #[Url(history: true)]
    public string $direction = 'desc';

    public ?int $commentIdBeingDeleted = null;

    public function sortBy(string $column): void
    {
        $this->direction = ($this->sort === $column && $this->direction === 'asc') ? 'desc' : 'asc';
        $this->sort = $column;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $this->form->setComment($comment);
        $this->dispatch('open-modal', name: 'edit-comment-modal');
    }

    public function save(): void
    {
        $this->authorize('update', $this->form->comment);

        $this->form->update();
        $this->dispatch('close-modal', name: 'edit-comment-modal');
        Toaster::success('Comentário atualizado com sucesso.');
    }

    public function confirmDelete(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);

        $this->commentIdBeingDeleted = $id;
        $this->dispatch('open-modal', name: 'confirm-comment-deletion');
    }

    public function delete(): void
    {
        if (!$this->commentIdBeingDeleted) {
            return;
        }

        $comment = Comment::findOrFail($this->commentIdBeingDeleted);
        $this->authorize('delete', $comment);

        $comment->delete();

        $this->commentIdBeingDeleted = null;
        $this->resetPage();
        Toaster::success('Comentário removido com sucesso.');
    }

    #[Computed]
    public function comments()
    {
        return app(ListCommentsAction::class)->exec(
            user: auth()->user(),
            filters: CommentFilterData::from([
                'search' => $this->search,
                'status' => $this->status,
                'sort' => $this->sort,
                'direction' => $this->direction,
            ]),
        );
    }

    public function render(): View
    {
        return view('livewire.dashboard.comments.comment-index');
    }
}
