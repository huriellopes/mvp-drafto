@props([
    'href' => '#',
    'active' => false,
])

<a
    href="{{ $href }}"
    title="{{ $slot }}"
    @class([
        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition-all duration-200',
        'bg-zinc-900 text-white shadow-md shadow-zinc-200' => $active,
        'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900' => ! $active,
        'justify-center lg:px-0' => false,
    ])
>
    <span @class([
        'flex h-6 w-6 shrink-0 items-center justify-center transition-transform duration-200',
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
