@props([
    'title' => null,
    'text' => null,
    'position' => 'top',
    'icon' => 'circle-help',
    'iconClass' => 'h-4 w-4',
    'panelClass' => 'w-64',
])

<div
    x-data="{
        show: false,
        pinned: false,
        currentPosition: '{{ $position }}',
        open() {
            this.show = true;
            this.calculatePosition();
        },
        toggle() {
            this.pinned = !this.pinned;
            this.show = this.pinned;
            if (this.show) this.calculatePosition();
        },
        close() {
            this.show = false;
            this.pinned = false;
        },
        calculatePosition() {
            this.currentPosition = '{{ $position }}';
            this.$nextTick(() => {
                const panel = this.$refs.panel;
                if (!panel) return;

                const rect = panel.getBoundingClientRect();
                const padding = 10; // Margem de segurança

                if (this.currentPosition === 'top' && rect.top < padding) {
                    this.currentPosition = 'bottom';
                } else if (this.currentPosition === 'bottom' && rect.bottom > window.innerHeight - padding) {
                    this.currentPosition = 'top';
                } else if (this.currentPosition === 'left' && rect.left < padding) {
                    this.currentPosition = 'right';
                } else if (this.currentPosition === 'right' && rect.right > window.innerWidth - padding) {
                    this.currentPosition = 'left';
                }
            });
        }
    }"
    @mouseenter="open()"
    @mouseleave="if (!pinned) show = false"
    @click.outside="close()"
    @keydown.escape.window="close()"
    class="relative inline-flex"
>
    <button
        type="button"
        @click.stop.prevent="toggle()"
        @focus="open()"
        @blur="if (!pinned) show = false"
        :aria-expanded="show"
        aria-label="{{ $title ?? __('Ajuda') }}"
        {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full text-zinc-400 transition hover:text-zinc-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-zinc-500 dark:hover:text-zinc-300']) }}
    >
        <x-dynamic-component :component="'lucide-' . $icon" :class="$iconClass" />
    </button>

    <div
        x-ref="panel"
        x-show="show"
        x-cloak
        role="tooltip"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :class="{
            'bottom-full left-1/2 -translate-x-1/2 mb-2': currentPosition === 'top',
            'top-full left-1/2 -translate-x-1/2 mt-2': currentPosition === 'bottom',
            'right-full top-1/2 -translate-y-1/2 mr-2': currentPosition === 'left',
            'left-full top-1/2 -translate-y-1/2 ml-2': currentPosition === 'right',
        }"
        class="absolute z-50 {{ $panelClass }} max-w-[calc(100vw-2rem)] rounded-xl border border-zinc-200 bg-white p-3 text-left text-xs font-normal normal-case not-italic leading-relaxed tracking-normal text-zinc-600 shadow-xl dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        style="display: none;"
    >
        @if ($title)
            <p class="mb-1 text-sm font-semibold text-zinc-900 dark:text-white">{{ $title }}</p>
        @endif

        <div class="space-y-1">
            {{ $text ?? $slot }}
        </div>

        <div
            :class="{
                'top-full left-1/2 -translate-x-1/2 border-t-white dark:border-t-zinc-800': currentPosition === 'top',
                'bottom-full left-1/2 -translate-x-1/2 border-b-white dark:border-b-zinc-800': currentPosition === 'bottom',
                'left-full top-1/2 -translate-y-1/2 border-l-white dark:border-l-zinc-800': currentPosition === 'left',
                'right-full top-1/2 -translate-y-1/2 border-r-white dark:border-r-zinc-800': currentPosition === 'right',
            }"
            class="absolute border-[6px] border-transparent"
        ></div>
    </div>
</div>
