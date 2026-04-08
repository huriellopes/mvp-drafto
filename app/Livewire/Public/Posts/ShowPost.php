<?php

declare(strict_types=1);

namespace App\Livewire\Public\Posts;

use App\Actions\Public\GetRelatedPostsAction;
use App\Enums\PostVisibilityEnum;
use App\Models\Post;
use App\Services\Post\PostSeoGenerator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowPost extends Component
{
    public Post $post;

    public function mount(string $slug): void
    {
        $this->post = Post::where('slug', $slug)
            ->published()
            ->with([
                'author.profile',
                'author.followers',
                'category',
                'tags'
            ])
            ->firstOrFail();

        if ($this->canReadContent) {
            $this->post->timestamps = false;
            $this->post->increment('views_count');
        }
    }

    #[Computed]
    public function relatedPosts()
    {
        return app(GetRelatedPostsAction::class)
            ->exec(
                post: $this->post
            )->posts;
    }

    #[Computed]
    public function canReadContent(): bool
    {
        // 1. Se for público, todos leem
        if ($this->post->visibility === PostVisibilityEnum::PUBLIC) {
            return true;
        }

        // 2. Se for unlisted, quem tem o link lê (estilo YouTube)
        if ($this->post->visibility === PostVisibilityEnum::UNLISTED) {
            return true;
        }

        // 3. Se for Followers Only
        if ($this->post->visibility === PostVisibilityEnum::FOLLOWERS_ONLY) {
            if (!auth()->check()) return false;

            $user = auth()->user();

            // Admin e o próprio Autor sempre leem
            if ($user->isAdmin() || $user->id === $this->post->user_id) {
                return true;
            }

            // Verifica se o usuário logado segue o autor
            return $user->isFollowing($this->post->author);
        }

        return false;
    }

    public function render(): View
    {
        $profile = $this->post->author->profile;

        return view('livewire.public.posts.show-post')
            ->layout('layouts.site', [
                'title' => $this->post->title . ' | Drafto',
                'primaryColor' => $profile->primary_color,
                'seo' => PostSeoGenerator::generate($this->post),
            ]);
    }
}
