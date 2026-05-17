<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Widgets;

use App\Enums\PostStatusEnum;
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
                ['title' => 'Total Usuários', 'value' => User::count(), 'desc' => 'Plataforma'],
                ['title' => 'Posts Publicados', 'value' => Post::where('status', PostStatusEnum::PUBLISHED)->count(), 'desc' => 'Global'],
                ['title' => 'Rascunhos Globais', 'value' => Post::where('status', PostStatusEnum::DRAFT)->count(), 'desc' => 'Em andamento'],
                ['title' => 'Denúncias', 'value' => Report::where('status', 'pending')->count(), 'desc' => 'Pendentes'],
                ['title' => 'Views Globais', 'value' => Post::sum('views_count'), 'desc' => 'Alcance total'],
            ];
        }

        if ($user->hasRole(RoleEnum::WRITER)) {
            return [
                ['title' => 'Posts Publicados', 'value' => $user->posts()->where('status', PostStatusEnum::PUBLISHED)->count(), 'desc' => 'Artigos visíveis'],
                ['title' => 'Meus Rascunhos', 'value' => $user->posts()->where('status', PostStatusEnum::DRAFT)->count(), 'desc' => 'Em andamento'],
                ['title' => 'Seguidores', 'value' => $user->followers()->count(), 'desc' => 'Pessoas te seguindo'],
                ['title' => 'Interações', 'value' => Comment::whereHas('post', fn ($q) => $q->where('user_id', $user->id))->count(), 'desc' => 'Comentários'],
                ['title' => 'Minhas Views', 'value' => $user->posts()->sum('views_count'), 'desc' => 'Total acumulado'],
            ];
        }

        // Visão para Reader (Leitor)
        return [
            ['title' => 'Artigos Lidos', 'value' => $user->postViews()->count(), 'desc' => 'Histórico'],
            ['title' => 'Posts Salvos', 'value' => $user->savedPosts()->count(), 'desc' => 'Sua biblioteca'],
            ['title' => 'Comentários', 'value' => $user->comments()->count(), 'desc' => 'Suas interações'],
            ['title' => 'Seguindo', 'value' => $user->following()->count(), 'desc' => 'Escritores'],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.stats-overview');
    }
}
