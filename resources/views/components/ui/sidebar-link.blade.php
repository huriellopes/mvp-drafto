@props([
    'href' => '#',
    'active' => false,
])

<a
    href="{{ $href }}"
    title="{{ $slot }}"
    @class([
        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold transition-all duration-300',
        'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20 dark:bg-indigo-500 dark:shadow-indigo-500/10' => $active,
        'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 hover:text-zinc-900 dark:hover:text-white' => ! $active,
    ])
>
    <span @class([
        'flex h-6 w-6 shrink-0 items-center justify-center transition-transform duration-300',
        'scale-110' => $active,
        'group-hover:scale-110' => ! $active,
    ])>
        {{ $icon ?? '' }}
    </span>

    <span
        x-show="!sidebarCollapsed"
        x-transition:enter="transition ease-out duration-200 delay-100"
        x-transition:enter-start="opacity-0 -translate-x-2"
        class="truncate"
    >
        {{ $slot }}
    </span>
</a>
