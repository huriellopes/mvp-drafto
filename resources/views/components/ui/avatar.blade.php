@props([
    'src' => null,
    'name' => null,
    'size' => 'md'
])

@php
    $sizes = [
        'xs' => 'h-6 w-6 text-[8px]',
        'sm' => 'h-8 w-8 text-[10px]',
        'md' => 'h-10 w-10 text-xs',
        'lg' => 'h-12 w-12 text-sm',
        'xl' => 'h-16 w-16 text-base',
        '2xl' => 'h-24 w-24 text-xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => "relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 $sizeClass"]) }}>
    @if($src)
        <img src="{{ $src }}" alt="{{ $name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
    @else
        <span class="font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
            {{ get_initials($name) }}
        </span>
    @endif
</div>
