<button
    wire:click="toggle"
    type="button"
    aria-pressed="{{ $isSaved ? 'true' : 'false' }}"
    aria-label="{{ $isSaved ? 'Remover dos salvos' : 'Salvar para ler depois' }}"
    @class([
        'flex h-10 w-10 items-center justify-center rounded-xl border transition shadow-sm active:scale-95',
        'border-profile-primary/20 bg-profile-primary/10 text-profile-primary' => $isSaved,
        'border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-profile-primary' => !$isSaved
    ])
    title="Salvar para ler depois"
>
    <x-lucide-bookmark @class(['h-5 w-5', 'fill-current' => $isSaved]) aria-hidden="true" />
</button>
