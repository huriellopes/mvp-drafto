<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Widgets;

use App\Actions\Dashboard\GetSuggestedWritersAction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SuggestedWriters extends Component
{
    /**
     * Sênior: Retorna as sugestões de escritores.
     * Para evitar erros de "Incomplete Object" e garantir que passamos instâncias reais
     * de Model para os subcomponentes (como follow-button), cacheamos apenas os IDs.
     */
    #[Computed]
    public function suggestions()
    {
        $userId = auth()->id();

        if (!$userId) {
            return collect();
        }

        // 1. Cacheamos apenas a lista de IDs sugeridos (Discovery logic)
        $suggestedIds = Cache::remember(
            "suggested_writers_ids_v6_{$userId}",
            now()->addHours(1),
            function () {
                $user = auth()->user();

                if (!$user) {
                    return [];
                }

                return resolve(GetSuggestedWritersAction::class)
                    ->exec($user)
                    ->pluck('id')
                    ->toArray();
            },
        );

        if (empty($suggestedIds)) {
            return collect();
        }

        // 2. Buscamos os modelos reais para garantir integridade no Blade e subcomponentes
        // Sênior: Utilizamos eager loading e preservamos a ordem do cache.
        return User::query()
            ->with(['profile'])
            ->withCount('publishedPosts')
            ->whereIn('id', $suggestedIds)
            ->get()
            ->sortBy(fn ($user) => array_search($user->id, $suggestedIds, true))
            ->values();
    }

    public function render(): View
    {
        return view('livewire.dashboard.widgets.suggested-writers');
    }
}
