<?php

declare(strict_types=1);

namespace App\Livewire\Public\Posts;

use App\Actions\Public\GetRelatedPostsAction;
use App\Enums\PostVisibilityEnum;
use App\Enums\RoleEnum;
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
        $this->post = Post::query()
            ->where('slug', $slug)
            ->published()
            ->with(['author.profile', 'category', 'tags'])
            ->firstOrFail();

        if ($this->canReadContent && $this->shouldIncrementView()) {
            $this->incrementViews();
        }
    }

    #[Computed]
    public function canReadContent(): bool
    {
        return match ($this->post->visibility) {
            PostVisibilityEnum::PUBLIC, PostVisibilityEnum::UNLISTED => true,
            PostVisibilityEnum::FOLLOWERS_ONLY => $this->checkFollowerAccess(),
            default => false,
        };
    }

    #[Computed]
    public function relatedPosts()
    {
        return app(GetRelatedPostsAction::class)->exec(post: $this->post)->posts;
    }

    public function render(): View
    {
        return view('livewire.public.posts.show-post')
            ->layout('layouts.site', [
                'title' => $this->post->title . ' | Drafto',
                'primaryColor' => $this->post->author->profile->primary_color,
                'seo' => PostSeoGenerator::generate($this->post),
            ]);
    }

    private function shouldIncrementView(): bool
    {
        if (auth()->check() && auth()->id() === $this->post->user_id) {
            return false;
        }

        return true;
    }

    private function incrementViews(): void
    {
        $this->post->timestamps = false;
        $this->post->increment('views_count');
    }

    private function checkFollowerAccess(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        if ($user->id === $this->post->user_id || $user->hasRole(RoleEnum::SUPER_ADMIN)) {
            return true;
        }

        return $user->isFollowing($this->post->author);
    }
}
