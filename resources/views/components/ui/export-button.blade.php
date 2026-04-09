@props(['loading' => null, 'variant' => 'primary'])

@php
    $target = $loading === true ? $attributes->whereStartsWith('wire:click')->first() : $loading;

    if ($target) {
        $target = preg_replace('/\(.*\)/', '', $target);
    }

    $variants = [
        'primary' => 'bg-zinc-900 text-white hover:bg-zinc-800',
        'secondary' => 'bg-white text-zinc-700 border border-zinc-200 hover:bg-zinc-50',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
    ];

    $class = $variants[$variant] ?? $variants['primary'];
@endphp

<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' => "inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-bold transition-all active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 {$class}"
    ]) }}
    @if($target)
        wire:loading.attr="disabled"
    wire:target="{{ $target }}"
    @endif
>
    @if($target)
        <span wire:loading.remove wire:target="{{ $target }}" class="flex items-center gap-2">
            {{ $slot }}
        </span>

        <span wire:loading wire:target="{{ $target }}">
            <div class="flex items-center gap-2">
                <x-lucide-loader-2 class="h-4 w-4 animate-spin" />
                <span>Exportando...</span>
            </div>
        </span>
    @else
        {{ $slot }}
    @endif
</button>
