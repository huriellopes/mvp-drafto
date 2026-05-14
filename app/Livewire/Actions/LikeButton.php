<?php

declare(strict_types=1);

namespace App\Livewire\Actions;

use App\Actions\Posts\ToggleLikeAction;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class LikeButton extends Component
{
    public Post $post;

    public function toggle()
    {
        app(ToggleLikeAction::class)
            ->exec(auth()->user(), $this->post, request()->ip());
        
        $this->post->refresh();
    }

    public function render(): View
    {
        $ip = request()->ip();
        $isLiked = auth()->check() 
            ? DB::table('post_likes')->where('post_id', $this->post->id)->where('user_id', auth()->id())->exists()
            : DB::table('post_likes')->where('post_id', $this->post->id)->whereNull('user_id')->where('ip_address', $ip)->exists();

        return view('livewire.actions.like-button', ['isLiked' => $isLiked]);
    }
}
