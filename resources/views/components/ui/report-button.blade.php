@props(['type', 'id'])

<button
    type="button"
    @click="$dispatch('openReportModal', { type: '{{ str_replace('\\', '\\\\', $type) }}', id: {{ $id }} })"
    {{ $attributes->merge([
        'class' => 'group relative flex h-12 w-12 items-center justify-center rounded-2xl border border-zinc-100 bg-zinc-50 text-zinc-400 transition-all duration-300 hover:border-red-200 hover:bg-red-50 hover:text-red-500 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-red-500/30 dark:hover:bg-red-500/10'
    ]) }}
    title="Reportar violação ou feedback"
>
    <x-lucide-flag class="h-5 w-5 transition-transform group-hover:scale-110 group-active:scale-90" />

    {{-- Tooltip sutil --}}
    <span class="absolute -top-10 left-1/2 -translate-x-1/2 scale-0 rounded-lg bg-zinc-900 px-2 py-1 text-[10px] font-bold text-white transition-all group-hover:scale-100 dark:bg-zinc-100 dark:text-zinc-900">
        Reportar
    </span>
</button>
