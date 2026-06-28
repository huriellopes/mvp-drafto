<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Widgets;

use App\Enums\RoleEnum;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecentActivity extends Component
{
    /**
     * Sênior: Retorna os itens de atividade recente.
     * Utilizamos cache apenas para a lista de IDs para evitar erros de serialização
     * e garantir que os modelos sejam sempre carregados de forma íntegra.
     */
    #[Computed]
    public function items()
    {
        $user = auth()->user();

        if (!$user) {
            return collect();
        }

        // 1. Cacheamos apenas a lista de IDs de posts (Discovery logic)
        $postIds = Cache::remember(
            'recent_activity_ids_v1_' . $user->id,
            now()->addMinutes(15),
            function () use ($user) {
                if ($user->isAdmin()) {
                    return Post::latest()->take(5)->pluck('id')->toArray();
                }

                if ($user->hasRole(RoleEnum::WRITER)) {
                    return $user->posts()->latest()->take(5)->pluck('id')->toArray();
                }

                return Post::query()
                    ->where(function ($query) use ($user) {
                        $query->whereHas('comments', fn ($q) => $q->where('user_id', $user->id))
                            ->orWhereHas('likedByUsers', fn ($q) => $q->where('user_id', $user->id))
                            ->orWhereHas('savedByUsers', fn ($q) => $q->where('user_id', $user->id));
                    })
                    ->latest()
                    ->take(6)
                    ->pluck('id')
                    ->toArray();
            },
        );

        if (empty($postIds)) {
            return collect();
        }

        // 2. Buscamos os modelos reais com os relacionamentos necessários.
        // Para curtida/salvo, usamos withExists (booleano) em vez de carregar as
        // coleções inteiras de usuários — um post popular hidrataria milhares de
        // User models só para um contains().
        return Post::query()
            ->with(['author.profile', 'category'])
            ->withExists([
                'likedByUsers as is_liked' => fn ($q) => $q->where('user_id', $user->id),
                'savedByUsers as is_saved' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->whereIn('id', $postIds)
            ->get()
            ->sortBy(fn ($post) => array_search($post->id, $postIds, true))
            ->values();
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
