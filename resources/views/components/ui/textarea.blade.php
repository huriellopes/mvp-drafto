@props([
    'label' => null,
    'error' => null,
    'rows' => 4
])

@php
    // Tenta capturar o ID do wire:model ou gera um random para o label funcionar
    $id = $attributes->get('id') ?? $attributes->wire('model')->value() ?? Str::random(8);
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $id }}" class="mb-2 block text-sm font-bold text-zinc-700 dark:text-zinc-300">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <textarea
            {{ $attributes->merge([
                'id' => $id,
                'rows' => $rows,
                'class' => 'block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition
                            focus:border-zinc-900 focus:ring-0 placeholder:text-zinc-400
                            dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:border-profile-primary
                            disabled:cursor-not-allowed disabled:opacity-50'
            ]) }}
        ></textarea>
    </div>

    @if($error)
        <p class="mt-2 text-sm text-red-600 dark:text-red-400 font-medium">
            {{ $error }}
        </p>
    @endif
</div>
