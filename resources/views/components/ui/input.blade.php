@props(['label' => null, 'error' => null])

@php
    $id = $attributes->get('id') ?? $attributes->wire('model')->value() ?? Str::random(8);
@endphp

<div>
    @if($label)
        <label for="{{ $id }}" class="mb-2 block text-sm font-medium text-zinc-700">
            {{ $label }}
        </label>
    @endif

    <input
        {{ $attributes->merge([
            'id' => $id,
            'class' => 'block w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:ring-0 placeholder:text-zinc-400'
        ]) }}
    >

    @if($error)
        <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
