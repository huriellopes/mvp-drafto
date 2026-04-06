<div>
    @if(auth()->check() && (auth()->id() !== $user->id))
        <button
            wire:click="toggle"
            wire:loading.attr="disabled"
            @class([
                'inline-flex h-12 w-full items-center justify-center rounded-2xl px-8 text-sm font-bold transition-all active:scale-95 disabled:opacity-50',
                'bg-profile-primary text-white shadow-lg shadow-profile-primary/20 hover:brightness-110' => !$isFollowing,
                'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50' => $isFollowing,
            ])
        >
            <span wire:loading.remove wire:target="toggle">
                {{ $isFollowing ? 'Seguindo' : 'Seguir' }}
            </span>
            <x-lucide-loader-2 wire:loading wire:target="toggle" class="h-4 w-4 animate-spin" />
        </button>
    @endif
</div>
