<button
    wire:click="toggle"
    type="button"
    aria-pressed="{{ $isLiked ? 'true' : 'false' }}"
    aria-label="{{ $isLiked ? 'Remover curtida' : 'Curtir' }}"
    @class([
        'flex h-10 items-center gap-2 rounded-xl border px-4 transition shadow-sm active:scale-95',
        'border-red-100 bg-red-50 text-red-600' => $isLiked,
        'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-red-500' => !$isLiked
    ])
>
    <x-lucide-heart @class(['h-5 w-5', 'fill-current' => $isLiked]) aria-hidden="true" />
    <span class="text-sm font-bold">{{ $post->likes_count }}</span>
</button>
