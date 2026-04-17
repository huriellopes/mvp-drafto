<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Posts;

use App\Actions\Posts\SavePostAction;
use App\Enums\PostStatusEnum;
use App\Livewire\Forms\Dashboard\PostForm;
use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ManagePost extends Component
{
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

        if (!$post && auth()->check() && auth()->user()->hasReachedPostLimit()) {
            Toaster::error('Você atingiu o limite de posts do seu plano. Faça upgrade para publicar mais.');

            return $this->redirect(route('dashboard.billing.plans'));
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

        $status = $this->post?->status ?? PostStatusEnum::DRAFT;
        $dto = $this->form->toDTO($this->updatedCoverPath, $status);

        try {
            $this->post = app(SavePostAction::class)->exec(
                auth()->user(),
                $dto,
                $this->post,
            );

            Toaster::success('Seu progresso foi salvo com sucesso!');

            if (request()->routeIs('dashboard.posts.create')) {
                return $this->redirect(route('dashboard.posts.edit', $this->post), navigate: true);
            }
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    public function publish()
    {
        $this->validate();
        
        $dto = $this->form->toDTO($this->updatedCoverPath, PostStatusEnum::PUBLISHED);

        try {
            $this->post = app(SavePostAction::class)->exec(
                auth()->user(),
                $dto,
                $this->post,
            );

            Toaster::success('Publicação realizada com sucesso!');
            
            return $this->redirect(route('dashboard.posts.index'), navigate: true);
        } catch (\Exception $e) {
            Toaster::error($e->getMessage());
        }
    }

    public function render(): View
    {
        // Busca categorias globais e as do usuário logado
        $categories = PostCategory::forUser(auth()->id())
            ->orderBy('user_id', 'asc') // Coloca as globais primeiro
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.dashboard.posts.manage-post', [
            'categories' => $categories,
        ])->layout('layouts.app', [
            'heading' => $this->post ? 'Editando: ' . $this->post->title : 'Nova Publicação',
        ]);
    }
}
