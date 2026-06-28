@props([
    'text',
    'position' => 'top',
    // Permite sobrescrever o wrapper (ex.: botões posicionados de forma absoluta,
    // onde o `relative inline-block` padrão quebraria o layout).
    'wrapperClass' => 'relative inline-block',
])

<div
    x-data="{
        tipShow: false,
        currentPosition: '{{ $position }}',
        calculatePosition() {
            this.currentPosition = '{{ $position }}';
            this.$nextTick(() => {
                const tooltip = this.$refs.tooltip;
                if (!tooltip) return;
                
                const rect = tooltip.getBoundingClientRect();
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
    @mouseenter="tipShow = true; calculatePosition()"
    @mouseleave="tipShow = false"
    class="{{ $wrapperClass }}"
>
    {{ $slot }}

    <div 
        x-ref="tooltip"
        x-show="tipShow"
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
        class="absolute z-50 whitespace-nowrap rounded-lg bg-zinc-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-xl dark:bg-zinc-800"
        style="display: none;"
    >
        {{ $text }}
        
        {{-- Seta (Arrow) --}}
        <div 
            :class="{
                'top-full left-1/2 -translate-x-1/2 border-t-zinc-900 dark:border-t-zinc-800': currentPosition === 'top',
                'bottom-full left-1/2 -translate-x-1/2 border-b-zinc-900 dark:border-b-zinc-800': currentPosition === 'bottom',
                'left-full top-1/2 -translate-y-1/2 border-l-zinc-900 dark:border-l-zinc-800': currentPosition === 'left',
                'right-full top-1/2 -translate-y-1/2 border-r-zinc-900 dark:border-r-zinc-800': currentPosition === 'right',
            }"
            class="absolute border-4 border-transparent"
        ></div>
    </div>
</div>
