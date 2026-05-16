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
use Illuminate\Validation\ValidationException;
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

    public bool $isSaving = false;

    public ?string $lastSavedAt = null;

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
        $this->save(isAutosave: true);
    }

    public function updated($property): void
    {
        // Só faz autosave se o post já existir e for um campo do formulário
        if ($this->post && str_starts_with($property, 'form.')) {
            $this->save(isAutosave: true);
        }
    }

    public function save(bool $isAutosave = false)
    {
        if ($isAutosave) {
            $this->isSaving = true;
        }

        try {
            // Se for rascunho, valida apenas o título para permitir progresso parcial
            if (!$this->post || $this->post->status === PostStatusEnum::DRAFT) {
                $this->form->validateForDraft();
            } else {
                $this->validate();
            }

            // Sênior: Proteção de Rate Limit para salvamento
            $executed = RateLimiter::attempt(
                'save-post:' . auth()->id(),
                $maxAttempts = 30, // Mais folga para autosave
                function () use ($isAutosave) {
                    $status = $this->post?->status ?? PostStatusEnum::DRAFT;
                    $dto = $this->form->toDTO($this->updatedCoverPath, $status);

                    $this->post = app(SavePostAction::class)->exec(
                        auth()->user(),
                        $dto,
                        $this->post,
                    );

                    $this->lastSavedAt = now()->format('H:i:s');

                    if (!$isAutosave) {
                        $this->notifySuccess('Seu progresso foi salvo com sucesso!');
                    }
                },
                decaySeconds: 60,
            );

            if (!$executed && !$isAutosave) {
                $this->notifyWarning('Você está salvando muito rápido. Aguarde alguns segundos.');
            }

            if (request()->routeIs('dashboard.posts.create') && $this->post) {
                return $this->redirect(route('dashboard.posts.edit', $this->post), navigate: true);
            }
        } catch (ValidationException $e) {
            if (!$isAutosave) {
                $this->notifyError('Verifique os campos obrigatórios para salvar.');

                throw $e;
            }
        } catch (Exception $e) {
            if (!$isAutosave) {
                $this->notifyError($e->getMessage());
            }
        } finally {
            $this->isSaving = false;
        }
    }

    public function publish()
    {
        try {
            // Publicação exige validação completa sempre
            $this->validate();

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
        } catch (ValidationException $e) {
            $this->notifyError('Preencha todos os campos obrigatórios antes de publicar.');

            throw $e;
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
