@props(['label' => 'Online', 'color' => 'bg-green-500'])

<div class="flex items-center gap-2 px-3 py-1.5 rounded-full border border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50">
    <span @class([
        'h-1.5 w-1.5 rounded-full animate-pulse',
        $color,
        'shadow-[0_0_8px_rgba(34,197,94,0.4)]' => $color === 'bg-green-500'
    ])></span>
    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
        {{ $label }}
    </span>
</div>
