<?php

declare(strict_types=1);

namespace App\Livewire\Public\Posts;

use App\Actions\Public\GetRelatedPostsAction;
use App\Enums\PostVisibilityEnum;
use App\Enums\RoleEnum;
use App\Models\Post;
use App\Models\User;
use App\Services\Post\PostSeoGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * @property-read bool $canReadContent
 * @property-read Collection $relatedPosts
 */
class ShowPost extends Component
{
    public Post $post;

    public function mount(string $slug): void
    {
        $this->post = Cache::remember(
            "post_show_{$slug}",
            now()->addDays(7),
            fn () => Post::query()
                ->where('slug', $slug)
                ->published()
                ->with(['author.profile', 'category', 'tags'])
                ->firstOrFail(),
        );

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

    #[Computed]
    public function renderedContent(): string
    {
        $content = $this->post->content;

        // Sênior: Interpreta tags <iframe> (sejam elas HTML puro ou entidades escapadas)
        // Isso resolve o problema de quando o usuário cola o código como texto no editor
        return preg_replace_callback(
            '/(?:<iframe|&lt;iframe)[^>]*?src=["\']([^"\']+)["\'][^>]*?(?:>|&gt;).*?(?:<\/iframe>|&lt;\/iframe&gt;)/is',
            function ($matches) {
                $rawTag = html_entity_decode($matches[0]);
                $src = html_entity_decode($matches[1]);

                // Só processa e renderiza se for YouTube ou Vimeo (Segurança)
                if (str_contains($src, 'youtube.com/embed') || str_contains($src, 'player.vimeo.com/video')) {

                    $finalTag = $rawTag;

                    // Sênior: Força a inclusão do referrerpolicy para evitar bloqueios de domínio (ex: FIFA)
                    if (str_contains($src, 'youtube.com') && !str_contains($rawTag, 'referrerpolicy')) {
                        $finalTag = str_replace('<iframe', '<iframe referrerpolicy="strict-origin-when-cross-origin"', $rawTag);
                    }

                    return '<div class="aspect-video w-full rounded-2xl overflow-hidden my-8 ring-1 ring-zinc-200 dark:ring-zinc-800 shadow-sm">' . $finalTag . '</div>';
                }

                return $matches[0];
            },
            $content,
        );
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

        /** @var User $user */
        $user = auth()->user();

        if ($user->id === $this->post->user_id || $user->hasRole(RoleEnum::SUPER_ADMIN)) {
            return true;
        }

        /** @var User $author */
        $author = $this->post->author;

        return $user->isFollowing($author);
    }
}
