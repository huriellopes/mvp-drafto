@props([
    'text',
    'position' => 'top',
])

@php
    $positionClasses = match ($position) {
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
        default => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    };

    $arrowClasses = match ($position) {
        'top' => 'top-full left-1/2 -translate-x-1/2 border-t-zinc-900 dark:border-t-zinc-800',
        'bottom' => 'bottom-full left-1/2 -translate-x-1/2 border-b-zinc-900 dark:border-b-zinc-800',
        'left' => 'left-full top-1/2 -translate-y-1/2 border-l-zinc-900 dark:border-l-zinc-800',
        'right' => 'right-full top-1/2 -translate-y-1/2 border-r-zinc-900 dark:border-r-zinc-800',
        default => 'top-full left-1/2 -translate-x-1/2 border-t-zinc-900 dark:border-t-zinc-800',
    };
@endphp

<div 
    x-data="{ show: false }" 
    @mouseenter="show = true" 
    @mouseleave="show = false"
    class="relative inline-block"
>
    {{ $slot }}

    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @class([
            'absolute z-50 whitespace-nowrap rounded-lg bg-zinc-900 px-2.5 py-1.5 text-xs font-medium text-white shadow-xl dark:bg-zinc-800',
            $positionClasses
        ])
        style="display: none;"
    >
        {{ $text }}
        
        {{-- Seta (Arrow) --}}
        <div @class([
            'absolute border-4 border-transparent',
            $arrowClasses
        ])></div>
    </div>
</div>
