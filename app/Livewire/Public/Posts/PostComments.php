<?php

declare(strict_types=1);

namespace App\Livewire\Public\Posts;

use App\Actions\Comments\StoreCommentAction;
use App\Livewire\Forms\Public\CommentForm;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PostComments extends Component
{
    public Post $post;

    public CommentForm $form;

    public ?int $replyingTo = null;

    public string $replyContent = '';

    public function setReply(int $commentId): void
    {
        if (auth()->guest()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->reset(['replyContent']);
        $this->replyingTo = $commentId;
        $this->form->parent_id = $commentId;
    }

    public function cancelReply(): void
    {
        $this->reset(['replyingTo', 'replyContent']);
    }

    public function save(): void
    {
        if (auth()->guest()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->validateOnly('form.content');

        app(StoreCommentAction::class)->exec(
            auth()->user(),
            $this->post,
            $this->form->all(),
        );

        $this->form->resetForm();
        Toaster::success('Comentário publicado!');
    }

    public function saveReply(): void
    {
        if (auth()->guest()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->validate([
            'replyContent' => 'required|string|min:3|max:1000',
        ]);

        app(StoreCommentAction::class)->exec(
            auth()->user(),
            $this->post,
            [
                'content' => $this->replyContent,
                'parent_id' => $this->replyingTo,
            ],
        );

        $this->reset(['replyingTo', 'replyContent']);
        Toaster::success('Resposta enviada!');
    }

    #[Computed]
    public function comments()
    {
        return $this->post->comments()
            ->whereNull('parent_id')
            ->with(['user.profile', 'replies.user.profile', 'replies.replies'])
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.public.posts.post-comments');
    }
}
