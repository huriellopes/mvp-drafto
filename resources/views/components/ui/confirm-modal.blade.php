@props([
    'name',
    'title',
    'content',
    'buttonText' => 'Confirmar',
    'variant' => 'primary',
    'action' => null
])

<x-ui.modal :name="$name" :title="$title">
    <p class="text-sm leading-6 text-zinc-600">
        {{ $content }}
    </p>

    <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <button
            type="button"
            x-on:click="$dispatch('close-modal', { name: '{{ $name }}' })"
            class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
        >
            Cancelar
        </button>

        <button
            type="button"
            @if($action) wire:click="{{ $action }}" @endif
            @class([
                'inline-flex items-center justify-center rounded-2xl px-4 py-3 text-sm font-medium text-white transition',
                'bg-zinc-900 hover:bg-zinc-800' => $variant === 'primary',
                'bg-red-600 hover:bg-red-700' => $variant === 'danger',
            ])
            x-on:click="$dispatch('close-modal', { name: '{{ $name }}' })"
        >
            {{ $buttonText }}
        </button>
    </div>
</x-ui.modal>
