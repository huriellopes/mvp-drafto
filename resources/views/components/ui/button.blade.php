@props([
    'variant' => 'primary',
    'size' => 'md',
    'loading' => null,
    'type' => 'button',
    'icon' => null,
    'href' => null,
    'tracking' => null,
    'trackingParams' => [],
])

@php
    $wireClick = $attributes->whereStartsWith('wire:click')->first();
    $target = ($loading === true && $wireClick) ? preg_replace('/\(.*\)/', '', $wireClick) : $loading;

    $variants = [
        'primary'   => 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 dark:bg-indigo-500 dark:hover:bg-indigo-400',
        'dark'      => 'bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 shadow-sm',
        'secondary' => 'bg-white text-zinc-700 border border-zinc-200 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-300 dark:border-zinc-800 dark:hover:bg-zinc-800 shadow-xs',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 shadow-md shadow-red-500/20',
        'ghost'     => 'bg-transparent text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-900',
        'outline'   => 'bg-transparent border border-zinc-200 text-zinc-900 hover:bg-zinc-50 dark:border-zinc-800 dark:text-white dark:hover:bg-zinc-900',
    ];

    $sizes = [
        'xs' => 'px-3 py-1.5 text-[10px] uppercase tracking-widest',
        'sm' => 'px-4 py-2 text-xs uppercase tracking-widest',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-base',
    ];

    $classes = "inline-flex items-center justify-center gap-2 font-black rounded-2xl transition-all active:scale-95 cursor-pointer disabled:cursor-not-allowed disabled:opacity-50 " .
               ($variants[$variant] ?? $variants['primary']) . " " .
               ($sizes[$size] ?? $sizes['md']);

    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{ $href ? "href=$href" : '' }}
    @if($tracking) data-tracking="{{ $tracking }}" @endif
    @if(!empty($trackingParams)) data-tracking-params="{{ json_encode($trackingParams) }}" @endif
    {{ $attributes->merge(['type' => $href ? null : $type, 'class' => $classes]) }}
    @if($target && !$href)
        wire:loading.attr="disabled"
        wire:target="{{ $target }}"
    @endif
>
    @if($icon && !$target)
        <x-dynamic-component :component="'lucide-' . $icon" class="h-4 w-4" />
    @endif

    <span @if($target && !$href) wire:loading.remove wire:target="{{ $target }}" @endif class="flex items-center gap-2">
        {{ $slot }}
    </span>

    @if($target && !$href)
        <span wire:loading wire:target="{{ $target }}">
            <div class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                @if($size !== 'xs') <span class="text-[10px] uppercase font-black">Processando...</span> @endif
            </div>
        </span>
    @endif
</{{ $tag }}>
