<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Posts;

use App\Actions\Posts\UploadCoverImageAction;
use App\Models\Post;
use Exception;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class CoverUpload extends Component
{
    use WithFileUploads;

    public ?Post $post = null;

    public $image;

    public $imageUrl;

    public $cropData;

    public bool $isCropped = false;

    public function mount(?Post $post = null): void
    {
        $this->post = $post;

        if ($this->post?->cover_image_path) {
            $this->isCropped = true;
        }
    }

    public function updatedImage(): void
    {
        $this->validate(['image' => 'image|max:5120']);
        $this->imageUrl = $this->image->temporaryUrl();
        $this->isCropped = false;
        $this->dispatch('image-uploaded');
    }

    public function saveCrop($data): void
    {
        $this->validate(['image' => 'required|image']);

        try {
            $path = app(UploadCoverImageAction::class)->exec(
                $this->image,
                $data,
            );

            $this->isCropped = true;
            $this->dispatch('cover-prepared', coverPath: $path);
            Toaster::info('Capa preparada!');
        } catch (Exception $e) {
            Toaster::error('Falha ao processar imagem.');
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.posts.cover-upload');
    }
}
