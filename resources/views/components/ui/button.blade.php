@props(['loading' => null])

<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' => 'inline-flex w-full items-center justify-center rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-60'
    ]) }}
    @if($loading) wire:loading.attr="disabled" wire:target="{{ $loading }}" @endif
>
    @if($loading)
        <span wire:loading.remove wire:target="{{ $loading }}">
            {{ $slot }}
        </span>
        <span wire:loading wire:target="{{ $loading }}" class="flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processando...
        </span>
    @else
        {{ $slot }}
    @endif
</button>
