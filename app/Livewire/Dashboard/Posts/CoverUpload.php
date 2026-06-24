<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Posts;

use App\Actions\Posts\UploadCoverImageAction;
use App\Models\Post;
use Exception;
use Illuminate\Support\Facades\Storage;
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

    public ?string $currentCoverPath = null;

    public function mount(?Post $post = null): void
    {
        $this->post = $post;

        if ($this->post?->cover_image_path) {
            $this->isCropped = true;
            $this->currentCoverPath = $this->post->cover_image_path;
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

            // Sênior: Atualizamos o path local para o preview refletir a mudança imediatamente
            $this->currentCoverPath = $path;
            $this->isCropped = true;

            // Limpa os estados de upload temporário
            $this->image = null;
            $this->imageUrl = null;

            $this->dispatch('cover-prepared', coverPath: $path);
            Toaster::info('Capa preparada!');
        } catch (Exception $e) {
            Toaster::error('Falha ao processar imagem.');
        }
    }

    public function removeCover(): void
    {
        if (!$this->currentCoverPath) {
            return;
        }

        try {
            // Sênior: Se o arquivo existir e não for uma URL externa, deleta do storage
            if (!str_starts_with($this->currentCoverPath, 'http')) {
                Storage::disk('public')->delete($this->currentCoverPath);
            }

            $this->currentCoverPath = null;
            $this->isCropped = false;

            if ($this->post) {
                $this->post->update(['cover_image_path' => null]);
            }

            $this->dispatch('cover-prepared', coverPath: null);
            Toaster::info('Capa removida com sucesso.');
        } catch (Exception $e) {
            Toaster::error('Falha ao remover a imagem.');
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.posts.cover-upload');
    }
}
