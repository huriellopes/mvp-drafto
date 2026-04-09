<button
    wire:click="toggle"
    @class([
        'flex items-center gap-1.5 text-xs font-bold transition-all active:scale-90',
        'text-red-500' => $isLiked,
        'text-zinc-400 hover:text-red-500' => !$isLiked
    ])
>
    <x-lucide-heart @class(['h-3.5 w-3.5', 'fill-current' => $isLiked]) />
    <span>{{ $likesCount > 0 ? $likesCount : '' }}</span>
</button>
