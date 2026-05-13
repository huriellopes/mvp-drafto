@props(['loading' => null, 'variant' => 'primary'])

<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'relative inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-3 text-sm font-bold transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none'
    ]) }}
    @if($variant === 'primary')
        style="background-color: var(--profile-primary); color: var(--profile-primary-text, white);"
    @endif
    @if($loading) wire:loading.attr="disabled" wire:target="{{ $loading }}" @endif
>
    <span @if($loading) wire:loading.remove wire:target="{{ $loading }}" @endif class="flex items-center gap-2">
        {{ $slot }}
    </span>

    @if($loading)
        <x-lucide-loader-2 wire:loading wire:target="{{ $loading }}" class="h-4 w-4 animate-spin" />
    @endif
</button>
