<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Posts;

use App\Actions\Posts\SavePostAction;
use App\Enums\PostStatusEnum;
use App\Livewire\Forms\Dashboard\PostForm;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Traits\Livewire\HasStandardResponses;
use Exception;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ManagePost extends Component
{
    use HasStandardResponses;

    public PostForm $form;

    public ?Post $post = null;

    // Caminho da imagem de capa atualizada via componente CoverUpload
    public ?string $updatedCoverPath = null;

    public function mount(?Post $post = null)
    {
        if ($post && $post->exists) {
            $this->authorize('update', $post);
            $this->post = $post;
            $this->form->setPost($post);
        }
    }

    #[On('cover-prepared')]
    public function setCover(string $coverPath)
    {
        $this->updatedCoverPath = $coverPath;
    }

    public function save()
    {
        $this->validate();

        try {
            // Sênior: Proteção de Rate Limit para salvamento
            $executed = RateLimiter::attempt(
                'save-post:' . auth()->id(),
                $maxAttempts = 10, // Permite 10 saves por minuto (autosave e etc)
                function () {
                    $status = $this->post?->status ?? PostStatusEnum::DRAFT;
                    $dto = $this->form->toDTO($this->updatedCoverPath, $status);

                    $this->post = app(SavePostAction::class)->exec(
                        auth()->user(),
                        $dto,
                        $this->post,
                    );
                },
                decaySeconds: 60,
            );

            if (!$executed) {
                $this->notifyWarning('Você está salvando muito rápido. Aguarde alguns segundos.');

                return;
            }

            $this->notifySuccess('Seu progresso foi salvo com sucesso!');

            if (request()->routeIs('dashboard.posts.create')) {
                return $this->redirect(route('dashboard.posts.edit', $this->post), navigate: true);
            }
        } catch (Exception $e) {
            $this->notifyError($e->getMessage());
        }
    }

    public function publish()
    {
        $this->validate();

        try {
            // Sênior: Proteção de Rate Limit para publicação (Mais restrito)
            $executed = RateLimiter::attempt(
                'publish-post:' . auth()->id(),
                $maxAttempts = 3,
                function () {
                    $dto = $this->form->toDTO($this->updatedCoverPath, PostStatusEnum::PUBLISHED);

                    $this->post = app(SavePostAction::class)->exec(
                        auth()->user(),
                        $dto,
                        $this->post,
                    );
                },
                decaySeconds: 60,
            );

            if (!$executed) {
                $this->notifyError('Muitas tentativas de publicação. Aguarde um momento.');

                return;
            }

            $this->notifySuccess('Publicação realizada com sucesso!');

            return $this->redirect(route('dashboard.posts.index'), navigate: true);
        } catch (Exception $e) {
            $this->notifyError($e->getMessage());
        }
    }

    public function render(): View
    {
        // Busca categorias globais e as do usuário logado
        $categories = PostCategory::forUser(auth()->id())
            ->orderBy('user_id', 'asc') // Coloca as globais primeiro
            ->orderBy('name', 'asc')
            ->get();

        $tags = Tag::query()
            ->whereNull('user_id')
            ->orWhere('user_id', auth()->id())
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.dashboard.posts.manage-post', [
            'categories' => $categories,
            'availableTags' => $tags,
        ])->layout('layouts.app', [
            'heading' => $this->post ? 'Editando: ' . $this->post->title : 'Nova Publicação',
        ]);
    }
}
