<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use App\Actions\Posts\ToggleSaveAction;
use App\Models\Post;
use Livewire\Component;

class SaveButton extends Component
{
    public Post $post;

    public function toggle()
    {
        if (auth()->guest()) {
            return $this->redirect(route('login'), navigate: true);
        }

        app(ToggleSaveAction::class)->exec(auth()->user(), $this->post);
    }

    public function render()
    {
        $isSaved = auth()->check() && auth()->user()->savedPosts()->where('post_id', $this->post->id)->exists();

        return view('livewire.actions.save-button', ['isSaved' => $isSaved]);
    }
}
