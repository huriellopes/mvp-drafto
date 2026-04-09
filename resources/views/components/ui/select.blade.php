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

    <div class="relative">
        <select
            {{ $attributes->merge([
                'id' => $id,
                'class' => 'block w-full !appearance-none bg-none rounded-2xl border border-zinc-300 bg-white pl-4 pr-10 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-900 focus:ring-0 disabled:opacity-50'
            ]) }}
        >
            {{ $slot }}
        </select>

        {{-- Nosso ícone personalizado --}}
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-zinc-400">
            <x-lucide-chevron-down class="h-4 w-4" />
        </div>
    </div>

    @if($error)
        <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
