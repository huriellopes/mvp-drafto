@props(['required' => false])

@if($required)
    <span class="rounded-full bg-red-50 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-red-600 dark:bg-red-500/10 dark:text-red-400">
        Obrigatório
    </span>
@else
    <span class="rounded-full bg-zinc-100 px-1.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
        Opcional
    </span>
@endif
