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
use Illuminate\Support\Facades\Gate;
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
        if (auth()->guest() && !$this->post->comments_enabled) {
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
        if (!$this->post->comments_enabled) {
            Toaster::error('Os comentários estão desativados para este post.');

            return;
        }

        if (!Gate::allows('create', Comment::class)) {
            if (auth()->guest()) {
                $this->redirect(route('login'), navigate: true);
            } else {
                Toaster::error('Você não tem permissão para comentar.');
            }

            return;
        }

        $this->validateOnly('form.content');

        try {
            // Sênior: Detecção robusta de imagens via Regex (Case Insensitive)
            $hasImages = preg_match('/<(img|object|embed|iframe)/i', $this->form->content);

            throw_if($hasImages && auth()->check() && !auth()->user()->getModuleSetting(ModuleEnum::COMMENTS, 'allow_images', true), Exception::class, 'Sua conta não tem permissão para o envio de mídia nos comentários.');

            throw_if($hasImages && auth()->guest(), Exception::class, 'Visitantes não podem enviar mídia nos comentários.');

            resolve(StoreCommentAction::class)->exec(
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
        if (!$this->post->comments_enabled) {
            Toaster::error('Os comentários estão desativados para este post.');

            return;
        }

        $parent = Comment::findOrFail($this->replyingTo);

        if (!Gate::allows('reply', $parent)) {
            Toaster::error('Você não tem permissão para responder ou esta conversa atingiu o limite.');

            return;
        }

        $this->validate([
            'replyContent' => 'required|string|min:3|max:1000',
        ]);

        resolve(StoreCommentAction::class)->exec(
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
