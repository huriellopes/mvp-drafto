<?php

namespace App\Livewire\Dashboard\Posts;

use App\Actions\Posts\UploadCoverImageAction;
use App\Models\Post;
use App\Models\PostCategory;
use App\Livewire\Forms\Dashboard\PostForm;
use App\Actions\Posts\SavePostAction;
use App\Enums\PostStatusEnum;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ManagePost extends Component
{
    use WithFileUploads;

    public PostForm $form;
    public ?Post $post = null;

    public $temporaryCover;
    public $cropData;

    public function mount(?Post $post = null)
    {
        if ($post && $post->exists) {
            $this->authorize('update', $post);
            $this->post = $post;
            $this->form->setPost($post);
        }
    }

    #[On('cover-prepared')]
    public function setCover($cropData, $temporaryFile)
    {
        $this->cropData = $cropData;
        // O temporaryFile enviado pelo Livewire via evento pode ser usado diretamente
        $this->temporaryCover = $temporaryFile;
    }

    public function save()
    {
        $this->validate();
        $data = $this->form->getAttributes();

        if ($this->temporaryCover && $this->cropData) {
            $path = app(UploadCoverImageAction::class)->exec(
                $this->temporaryCover,
                $this->cropData
            );
            $data['cover_image_path'] = $path; // Atualiza o path no array final
        }

        $this->post = app(SavePostAction::class)->exec(
            auth()->user(),
            $data,
            $this->post
        );

        Toaster::success('Seu progresso foi salvo com sucesso!');

        if (request()->routeIs('dashboard.posts.create')) {
            return $this->redirect(route('dashboard.posts.edit', $this->post), navigate: true);
        }
    }

    public function publish()
    {
        $this->save();

        $this->post->update([
            'status' => PostStatusEnum::PUBLISHED,
            'published_at' => now()
        ]);

        Toaster::success('Publicação realizada com sucesso!');
    }

    public function render() : View
    {
        return view('livewire.dashboard.posts.manage-post', [
            'categories' => PostCategory::orderBy('name')->get()
        ])->layout('layouts.app', [
            'heading' => $this->post ? 'Editando: ' . $this->post->title : 'Nova Publicação'
        ]);
    }
}
