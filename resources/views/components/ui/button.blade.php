@props([
    'variant' => 'primary',
    'size' => 'md',
    'loading' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $wireClick = $attributes->whereStartsWith('wire:click')->first();
    $target = ($loading === true && $wireClick) ? preg_replace('/\(.*\)/', '', $wireClick) : $loading;

    $variants = [
        'primary'   => 'bg-zinc-900 text-white hover:bg-zinc-800 shadow-sm',
        'secondary' => 'bg-white text-zinc-700 border border-zinc-200 hover:bg-zinc-50 shadow-xs',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 shadow-sm',
        'ghost'     => 'bg-transparent text-zinc-600 hover:bg-zinc-100',
        'outline'   => 'bg-transparent border-2 border-zinc-900 text-zinc-900 hover:bg-zinc-900 hover:text-white',
    ];

    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs',
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $classes = "inline-flex items-center justify-center gap-2 font-bold rounded-2xl transition-all active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 " .
               ($variants[$variant] ?? $variants['primary']) . " " .
               ($sizes[$size] ?? $sizes['md']);
@endphp

<button
    {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}
    @if($target)
        wire:loading.attr="disabled"
    wire:target="{{ $target }}"
    @endif
>
    @if($icon && !$target)
        <x-dynamic-component :component="'lucide-' . $icon" class="h-4 w-4" />
    @endif

    <span @if($target) wire:loading.remove wire:target="{{ $target }}" @endif class="flex items-center gap-2">
        {{ $slot }}
    </span>

    @if($target)
        <span wire:loading wire:target="{{ $target }}">
            <div class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                @if($size !== 'xs') <span>Processando...</span> @endif
            </div>
        </span>
    @endif
</button>
