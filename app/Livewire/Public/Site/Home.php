<?php

declare(strict_types=1);

namespace App\Livewire\Public\Site;

use App\Actions\Public\ListPublicPostsAction;
use App\DTOs\Public\HomeDataDTO;
use App\Models\{Post, User, PostCategory};
use Livewire\Component;
use Livewire\Attributes\Computed;

class Home extends Component
{
    #[Computed]
    public function categories()
    {
        return PostCategory::withCount('posts')->orderBy('posts_count', 'desc')->take(8)->get();
    }

    public function render()
    {
        // Pegamos apenas os 5 últimos posts publicados via Action
        $posts = app(ListPublicPostsAction::class)->exec([
            'per_page' => 5
        ]);

        $data = new HomeDataDTO(
            totalPosts: Post::published()->count(),
            totalUsers: User::count(),
            featuredWriters: User::whereHas('posts')->with('profile')->latest()->take(12)->get(),
            posts: $posts,
            categories: $this->categories()
        );

        return view('livewire.public.site.home', ['data' => $data])
            ->layout('layouts.site');
    }
}
