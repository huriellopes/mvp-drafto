<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Widgets;

use App\Enums\RoleEnum;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentActivity extends Component
{
    #[Computed]
    public function items()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin vê os últimos posts de todos e novas denúncias
            return Post::with(['user', 'category'])->latest()->take(5)->get();
        }

        if ($user->hasRole(RoleEnum::WRITER)) {
            // Escritor vê seus últimos posts (rascunhos ou publicados)
            return $user->posts()->with('category')->latest()->take(5)->get();
        }

        // Leitor vê os posts que ele interagiu (curtiu ou comentou)
        return Post::query()
            ->where(function ($query) use ($user) {
                $query->whereHas('comments', fn($q) => $q->where('user_id', $user->id))
                    ->orWhereHas('likedByUsers', fn($q) => $q->where('user_id', $user->id))
                    ->orWhereHas('savedByUsers', fn($q) => $q->where('user_id', $user->id));
            })
            ->with(['author.profile', 'category'])
            ->latest()
            ->take(6)
            ->get();
    }

    public function placeholder(): string
    {
        return <<<'HTML'
            <div class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
                <div class="h-6 w-48 animate-pulse rounded-lg bg-zinc-100"></div>
                <div class="mt-6 space-y-4">
                    @foreach(range(1,3) as $i) <div class="h-20 animate-pulse rounded-2xl bg-zinc-50"></div> @endforeach
                </div>
            </div>
        HTML;
    }

    public function render(): View
    {
        return view('livewire.dashboard.widgets.recent-activity');
    }
}
