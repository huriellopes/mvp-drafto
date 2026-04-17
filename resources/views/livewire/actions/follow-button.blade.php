@props([
    'compact' => false,
    'iconOnly' => false
])

<div class="{{ $iconOnly ? '' : 'w-full' }}">
    @if(auth()->check() && (auth()->id() !== $user->id))
        <button
            wire:click="toggle"
            wire:loading.attr="disabled"
            title="{{ $this->isFollowing ? 'Deixar de seguir' : 'Seguir' }}"
            @class([
                // Classes Base
                'inline-flex items-center justify-center font-bold transition-all active:scale-95 disabled:opacity-50 shrink-0 select-none',

                // Modo Somente Ícone
                'h-11 w-11 rounded-2xl border shadow-sm' => $iconOnly,

                // Modo Compacto
                'h-8 px-4 text-[10px] uppercase tracking-widest rounded-xl border w-full shadow-xs' => $compact && !$iconOnly,

                // Modo Normal
                'h-12 w-full px-8 text-sm rounded-[1.25rem] border shadow-sm' => !$compact && !$iconOnly,

                // ESTADO: SEGUINDO
                'bg-emerald-50 border-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-500/20'
                    => $this->isFollowing,

                // ESTADO: NÃO SEGUINDO
                'bg-indigo-600 border-indigo-600 text-white dark:bg-indigo-500 dark:border-indigo-500 dark:text-white hover:bg-indigo-700 dark:hover:bg-indigo-400 hover:shadow-lg dark:hover:shadow-indigo-500/10 shadow-indigo-200'
                    => !$this->isFollowing,
            ])
        >
            {{-- Conteúdo do Botão --}}
            <div wire:loading.remove wire:target="toggle" class="flex items-center gap-2">
                @if($this->isFollowing)
                    <x-lucide-user-check @class([($compact || $iconOnly) ? 'h-4 w-4' : 'h-5 w-5']) />
                    @if(!$iconOnly) <span>Seguindo</span> @endif
                @else
                    <x-lucide-user-plus @class([($compact || $iconOnly) ? 'h-4 w-4' : 'h-5 w-5']) />
                    @if(!$iconOnly) <span>Seguir</span> @endif
                @endif
            </div>

            {{-- Estado de Loading --}}
            <x-lucide-loader-2 wire:loading wire:target="toggle" class="animate-spin h-4 w-4" />
        </button>
    @endif
</div>
