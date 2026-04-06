<?php

namespace App\Livewire\Dashboard\Posts;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

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
        $this->cropData = $data;
        $this->isCropped = true;
        $this->dispatch('cover-prepared', cropData: $data, temporaryFile: $this->image);
    }

    public function render(): View
    {
        return view('livewire.dashboard.posts.cover-upload');
    }
}
