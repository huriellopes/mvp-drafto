<?php

namespace App\Livewire\Public\Posts;

use App\Models\Post;
use App\Models\Comment;
use App\Livewire\Forms\Public\CommentForm;
use App\Actions\Comments\StoreCommentAction;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PostComments extends Component
{
    public Post $post;
    public CommentForm $form;
    public ?int $replyingTo = null;

    public function setReply(int $commentId)
    {
        if (auth()->guest()) return $this->redirect(route('login'), navigate: true);
        $this->replyingTo = $commentId;
        $this->form->parent_id = $commentId;
    }

    public function cancelReply()
    {
        $this->replyingTo = null;
        $this->form->parent_id = null;
    }

    public function save()
    {
        if (auth()->guest()) return $this->redirect(route('login'), navigate: true);

        app(StoreCommentAction::class)->exec(
            auth()->user(),
            $this->post,
            $this->form->all()
        );

        $this->form->resetForm();
        $this->replyingTo = null;
        $this->dispatch('notify', message: 'Comentário enviado!');
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

    public function render() : View
    {
        return view('livewire.public.posts.post-comments');
    }
}
