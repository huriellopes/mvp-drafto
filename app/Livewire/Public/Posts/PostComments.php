<?php

declare(strict_types=1);

namespace App\Livewire\Public\Posts;

use App\Actions\Comments\StoreCommentAction;
use App\DTOs\SaveCommentData;
use App\Enums\ModuleEnum;
use App\Livewire\Forms\Public\CommentForm;
use App\Models\Comment;
use App\Models\Post;
use Exception;
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
        $this->authorize('create', Comment::class);

        if (auth()->guest()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->validateOnly('form.content');

        try {
            // Sênior: Detecção robusta de imagens via Regex (Case Insensitive)
            $hasImages = preg_match('/<(img|object|embed|iframe)/i', $this->form->content);

            if ($hasImages && !auth()->user()->getModuleSetting(ModuleEnum::COMMENTS, 'allow_images')) {
                throw new Exception('Seu plano atual não permite o envio de mídia nos comentários.');
            }

            app(StoreCommentAction::class)->exec(
                auth()->user(),
                $this->post,
                SaveCommentData::from($this->form->all()),
            );
        } catch (Exception $e) {
            $this->addError('form.content', $e->getMessage());

            return;
        }

        $this->form->resetForm();
        Toaster::success('Comentário publicado!');
    }

    public function saveReply(): void
    {
        if (auth()->guest()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $parent = Comment::findOrFail($this->replyingTo);

        if (!$this->user()->can('reply', $parent)) {
            Toaster::error('Esta conversa atingiu o limite de respostas para este plano.');

            return;
        }

        $this->validate([
            'replyContent' => 'required|string|min:3|max:1000',
        ]);

        app(StoreCommentAction::class)->exec(
            auth()->user(),
            $this->post,
            SaveCommentData::from([
                'content' => $this->replyContent,
                'parent_id' => $this->replyingTo,
            ]),
        );

        $this->reset(['replyingTo', 'replyContent']);
        Toaster::success('Resposta enviada!');
    }

    #[Computed]
    public function comments()
    {
        return $this->post->comments()
            ->root()
            ->visible()
            ->withRelations()
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.public.posts.post-comments');
    }
}
