@props([
    'compact' => false,
    'iconOnly' => false,
    'inline' => false
])

<div class="{{ ($iconOnly || $inline) ? '' : 'w-full' }}">
    @if(auth()->check() && (auth()->id() !== $user->id))
        <button
            wire:click="toggle"
            wire:loading.attr="disabled"
            title="{{ $this->isFollowing ? 'Deixar de seguir' : 'Seguir' }}"
            @class([
                // Classes Base
                'inline-flex items-center justify-center font-bold transition-all active:scale-95 disabled:opacity-50 shrink-0 select-none',

                // Modo Somente Ícone
                'h-11 w-11 rounded-2xl border' => $iconOnly,

                // Modo Compacto
                'h-8 px-4 text-[10px] uppercase tracking-widest rounded-xl border w-full' => $compact && !$iconOnly && !$inline,

                // Modo Inline (largura automática, alinhado aos demais botões do header)
                'h-12 w-auto px-6 text-sm rounded-profile-button border' => $inline && !$iconOnly,

                // Modo Normal
                'h-12 w-full px-8 text-sm rounded-[1.25rem] border' => !$compact && !$iconOnly && !$inline,

                // ESTADO: SEGUINDO (Verde sutil)
                'bg-emerald-600 border-emerald-600 text-white dark:bg-emerald-500 dark:border-emerald-500 hover:bg-emerald-700 dark:hover:bg-emerald-400'
                    => $this->isFollowing,

                // ESTADO: NÃO SEGUINDO (cor do perfil, ex-indigo fixo)
                'bg-profile-primary border-profile-primary text-white hover:opacity-90'
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
