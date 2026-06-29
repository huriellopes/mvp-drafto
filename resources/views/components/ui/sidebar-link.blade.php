@props([
    'href' => '#',
    'active' => false,
    'tracking' => null,
    'trackingParams' => [],
])

<a
    href="{{ $href }}"
    @if($tracking) data-tracking="{{ $tracking }}" @endif
    @if(!empty($trackingParams)) data-tracking-params="{{ json_encode($trackingParams) }}" @endif
    x-data="{
        tipShow: false,
        tipX: 0,
        tipY: 0,
        showTip(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            this.tipX = rect.right + 12;
            this.tipY = rect.top + (rect.height / 2);
            this.tipShow = true;
        },
    }"
    @mouseenter="if (sidebarCollapsed && ! sidebarOpen) showTip($event)"
    @mouseleave="tipShow = false"
    aria-label="{{ $slot }}"
    {{ $attributes->merge([
        'class' => 'group relative flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold transition-all duration-300 ' .
                   ($active ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20 dark:bg-indigo-500 dark:shadow-indigo-500/10' : 'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 hover:text-zinc-900 dark:hover:text-white')
    ]) }}
>
    <span @class([
        'flex h-6 w-6 shrink-0 items-center justify-center transition-transform duration-300',
        'scale-110' => $active,
        'group-hover:scale-110' => ! $active,
    ])>
        {{ $icon ?? '' }}
    </span>

    <span
        x-show="!sidebarCollapsed || sidebarOpen"
        x-transition:enter="transition ease-out duration-200 delay-100"
        x-transition:enter-start="opacity-0 -translate-x-2"
        class="truncate"
    >
        {{ $slot }}
    </span>

    {{-- Tooltip exibido apenas com a sidebar recolhida.
         Teleportado para o <body> e posicionado de forma fixed para escapar do
         overflow do menu e ficar com z-index acima de todo o conteúdo. --}}
    <template x-teleport="body">
        <span
            x-show="tipShow"
            x-cloak
            x-transition.opacity.duration.150ms
            :style="`left: ${tipX}px; top: ${tipY}px;`"
            class="pointer-events-none fixed z-[100] -translate-y-1/2 whitespace-nowrap rounded-lg bg-zinc-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-xl dark:bg-zinc-800"
        >
            {{ $slot }}
        </span>
    </template>
</a>
