@use(App\Models\Post)
@use(App\Enums\RoleEnum)
<x-ui.section-card
    title="{{ auth()->user()->hasRole(RoleEnum::WRITER) ? 'Seus conteúdos recentes' : 'Atividade recente' }}"
    description="Últimas movimentações na plataforma."
>
    <div class="space-y-4">
        @forelse ($this->items as $item)
            @if($item instanceof Post)
                <x-dashboard.recent-post-card :post="$item" />
            @endif
        @empty
            <x-ui.empty-state
                title="Nenhuma atividade encontrada"
                description="Comece a explorar a plataforma para ver dados aqui."
            />
        @endforelse
    </div>
</x-ui.section-card>
