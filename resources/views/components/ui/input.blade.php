@props(['label' => null, 'error' => null, 'prefix' => null, 'suffix' => null])

@php
    $id = $attributes->get('id') ?? ($attributes->wire('model') ? $attributes->wire('model')->value() : 'input-' . Str::random(8));
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $id }}" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ $label }}
        </label>
    @endif

    <div @class([
        'flex items-center rounded-2xl border bg-white dark:bg-zinc-900 transition-all focus-within:ring-2 focus-within:ring-profile-primary/20',
        'border-zinc-300 dark:border-zinc-800 focus-within:border-profile-primary' => !$error,
        'border-red-500 focus-within:border-red-500' => $error,
    ])>
        @if($prefix)
            <span class="pl-4 pr-2 text-zinc-400 font-medium select-none">
                {{ $prefix }}
            </span>
        @endif

            <input
            {{ $attributes->merge([
                'id' => $id,
                'autocomplete' => 'off',
            'spellcheck' => 'false',
            'class' => 'block w-full bg-transparent px-4 py-3 text-sm text-zinc-900 dark:text-white outline-none placeholder:text-zinc-400 focus:placeholder:opacity-50 transition-all'
            ]) }}
            />

        @if($suffix)
            <span class="pr-4 pl-2 text-zinc-400 font-medium select-none">
                {{ $suffix }}
            </span>
        @endif
    </div>

    @if($error)
        <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
