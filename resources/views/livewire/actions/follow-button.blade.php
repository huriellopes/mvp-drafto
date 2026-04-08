@props(['compact' => false])

<div>
    @if(auth()->check() && (auth()->id() !== $user->id))
        <button
            wire:click="toggle"
            wire:loading.attr="disabled"
            @class([
                // Classes Base: Layout e Transição
                'inline-flex items-center justify-center font-bold transition-all active:scale-95 disabled:opacity-50',

                // Modo Compacto (Para o widget de sugestões)
                'h-8 px-4 text-[10px] uppercase tracking-widest rounded-xl border w-full' => $compact,

                // Modo Normal (Para perfil/sidebar)
                'h-12 w-full px-8 text-sm rounded-2xl border' => !$compact,

                // ESTADO: SEGUINDO (Light/Dark Adaptativo)
                // Usamos fundos neutros e bordas sutis
                'bg-zinc-100 border-zinc-200 text-zinc-900 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100 hover:bg-zinc-200 dark:hover:bg-zinc-700'
                    => $isFollowing,

                // ESTADO: NÃO SEGUINDO (Destaque)
                // Aqui o texto PRECISA ser branco para contrastar com a cor primária (Zinc-900 ou Brand Color)
                'bg-zinc-900 border-zinc-900 text-white dark:bg-white dark:border-white dark:text-zinc-900 shadow-sm hover:opacity-90'
                    => !$isFollowing,
            ])
        >
            {{-- Conteúdo do Botão --}}
            <div wire:loading.remove wire:target="toggle" class="flex items-center gap-2">
                @if($isFollowing)
                    <x-lucide-user-check @class([$compact ? 'h-3 w-3' : 'h-4 w-4']) />
                    <span>Seguindo</span>
                @else
                    <x-lucide-user-plus @class([$compact ? 'h-3 w-3' : 'h-4 w-4']) />
                    <span>Seguir</span>
                @endif
            </div>

            {{-- Estado de Loading --}}
            <x-lucide-loader-2 wire:loading wire:target="toggle" class="animate-spin {{ $compact ? 'h-3 w-3' : 'h-4 w-4' }}" />
        </button>
    @endif
</div>
