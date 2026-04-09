<?php

declare(strict_types=1);

namespace App\Livewire\Public\Site;

use App\Actions\Public\ListPublicPostsAction;
use App\DTOs\Public\HomeDataDTO;
use App\DTOs\Public\PostFilterData;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use RalphJSmit\Laravel\SEO\Support\SEOData;

#[Layout('layouts.site', [
    'seo' => new SEOData(
        title: 'Drafto | Escreva seu Legado',
        description: 'A plataforma onde grandes ideias ganham vida. Conecte-se e inspire.',
    ),
])]
#[Lazy]
class Home extends Component
{
    public function placeholder(): View
    {
        // Passamos uma versão "vazia" do DTO ou apenas a view para performance máxima
        return view('livewire.public.site.placeholders.home');
    }

    #[Computed]
    public function featuredWriters()
    {
        return User::query()
            ->whereHas('posts')
            ->with(['profile'])
            ->withCount('publishedPosts')
            ->withCount('followers')
            ->latest()
            ->take(12)
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return PostCategory::query()
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->take(8)
            ->get();
    }

    public function render(): View
    {
        $posts = app(ListPublicPostsAction::class)
            ->exec(PostFilterData::from([
                'per_page' => 6,
            ]));

        $stats = $this->getHomeStats();

        // 3. Montamos o DTO
        $data = new HomeDataDTO(
            totalPosts: $stats['totalPosts'],
            totalUsers: $stats['totalUsers'],
            featuredWriters: $this->featuredWriters(),
            posts: $posts,
            categories: $this->categories(),
        );

        return view('livewire.public.site.home', ['data' => $data]);
    }

    private function getHomeStats(): array
    {
        return Cache::remember('drafto_home_stats', now()->addMinutes(10), fn () => [
            'totalPosts' => Post::published()->count(),
            'totalUsers' => User::count(),
        ]);
    }
}
