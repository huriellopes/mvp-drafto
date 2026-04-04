<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Widgets;

use App\Enums\RoleEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class StatsOverview extends Component
{
    #[Computed]
    public function stats(): array
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return [
                ['title' => 'Total Usuários', 'value' => User::count(), 'desc' => 'Usuários registrados'],
                ['title' => 'Total Posts', 'value' => Post::count(), 'desc' => 'Posts na plataforma'],
                ['title' => 'Denúncias', 'value' => Report::query()->where('status', 'pending')->count(), 'desc' => 'Pendentes de revisão'],
                ['title' => 'Visualizações', 'value' => Post::sum('views_count'), 'desc' => 'Alcance global'],
            ];
        }

        if ($user->hasRole(RoleEnum::WRITER)) {
            return [
                ['title' => 'Meus Conteúdos', 'value' => $user->posts()->count(), 'desc' => 'Total de publicações'],
                ['title' => 'Publicados', 'value' => $user->posts()->published()->count(), 'desc' => 'Visíveis ao público'],
                ['title' => 'Comentários', 'value' => Comment::whereHas('post', fn ($q) => $q->where('user_id', $user->id))->count(), 'desc' => 'Interações'],
                ['title' => 'Minhas Views', 'value' => $user->posts()->sum('views_count'), 'desc' => 'Leituras totais'],
            ];
        }

        // Visão para Leitor (Reader)
        return [
            ['title' => 'Lidos', 'value' => $user->postViews()->count(), 'desc' => 'Artigos que você leu'],
            ['title' => 'Salvos', 'value' => $user->savedPosts()->count(), 'desc' => 'Para ler mais tarde'],
            ['title' => 'Comentários', 'value' => $user->comments()->count(), 'desc' => 'Suas participações'],
            ['title' => 'Seguindo', 'value' => $user->following()->count(), 'desc' => 'Escritores favoritos'],
        ];
    }

    public function placeholder(): string
    {
        return <<<'HTML'
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 animate-pulse">
                @foreach(range(1,4) as $i) <div class="h-32 rounded-3xl bg-zinc-100"></div> @endforeach
            </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.stats-overview');
    }
}
